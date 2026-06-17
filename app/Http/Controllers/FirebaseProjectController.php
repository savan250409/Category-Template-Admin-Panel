<?php

namespace App\Http\Controllers;

use App\Models\FirebaseProject;
use App\Support\EnvWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Manage FCM credentials for every app this admin panel serves.
 *
 * The admin uploads two JSON files from the Firebase console:
 *   - Service Account JSON (required) — used to authenticate FCM v1 calls.
 *   - google-services.json (optional) — auto-fills project_id, sender_id
 *     and the topic (the Android package name).
 *
 * Everything is stored in the database (the runtime source of truth) AND
 * mirrored into the .env file automatically on every save/delete, so the
 * config is generated for you — nothing has to be edited by hand:
 *
 *   FIREBASE_KEY_<KEY>_PROJECT_ID
 *   FIREBASE_KEY_<KEY>_SENDER_ID
 *   FIREBASE_KEY_<KEY>_TOPIC
 *   FIREBASE_KEY_<KEY>_CREDENTIALS   (path to the saved service-account JSON)
 *   FIREBASE_DEFAULT_PROJECT         (key of the default project)
 */
class FirebaseProjectController extends Controller
{
    public function index()
    {
        $projects = FirebaseProject::orderByDesc('is_default')->orderBy('display_name')->get();
        return view('notifications.firebase.index', compact('projects'));
    }

    public function create()
    {
        return view('notifications.firebase.form');
    }

    public function store(Request $request)
    {
        $data = $this->validateAndExtract($request, null);

        $this->persist(new FirebaseProject(), $data, $request->boolean('is_default'));
        $envOk = $this->syncFirebaseEnv();

        return redirect()->route('firebase-projects.index')
            ->with('success', 'Firebase project added successfully.' . $this->envNote($envOk));
    }

    public function edit($id)
    {
        $project = FirebaseProject::findOrFail($id);
        return view('notifications.firebase.form', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $project = FirebaseProject::findOrFail($id);
        $data    = $this->validateAndExtract($request, $project);

        $oldKey = $project->getOriginal('key');
        $this->persist($project, $data, $request->boolean('is_default'));

        // If the key changed, drop the old key's stale .env entries + file.
        if ($oldKey && $oldKey !== $project->key) {
            EnvWriter::removeByPrefix('FIREBASE_KEY_' . strtoupper($oldKey) . '_');
            @File::delete($this->credentialsFilePath($oldKey));
        }

        $envOk = $this->syncFirebaseEnv();

        return redirect()->route('firebase-projects.index')
            ->with('success', 'Firebase project updated successfully.' . $this->envNote($envOk));
    }

    public function destroy($id)
    {
        $project = FirebaseProject::findOrFail($id);
        $wasDefault = $project->is_default;
        $key = $project->key;
        $project->delete();

        // Promote another project to default if we removed the default one.
        if ($wasDefault) {
            $next = FirebaseProject::first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        // Remove this project's .env keys + service-account file, then resync.
        EnvWriter::removeByPrefix('FIREBASE_KEY_' . strtoupper($key) . '_');
        @File::delete($this->credentialsFilePath($key));
        $this->syncFirebaseEnv();

        return redirect()->route('firebase-projects.index')
            ->with('success', 'Firebase project deleted.');
    }

    /* -----------------------------------------------------------------
     | .env mirroring
     |-----------------------------------------------------------------*/

    /**
     * Regenerate every FIREBASE_KEY_* entry (and FIREBASE_DEFAULT_PROJECT)
     * in .env from the database, and write each project's service-account
     * JSON to a file the .env path points at. Rebuilding the whole block
     * keeps .env an exact mirror of the DB — renames/deletes self-heal.
     *
     * @return bool whether the .env file was written successfully
     */
    protected function syncFirebaseEnv(): bool
    {
        $projects = FirebaseProject::orderBy('id')->get();

        // Clear the managed block first so removed projects don't linger.
        EnvWriter::removeByPrefix('FIREBASE_KEY_');
        EnvWriter::remove(['FIREBASE_DEFAULT_PROJECT']);

        $pairs   = [];
        $default = null;

        foreach ($projects as $p) {
            $K = strtoupper($p->key);

            $pairs["FIREBASE_KEY_{$K}_PROJECT_ID"] = (string) $p->project_id;
            $pairs["FIREBASE_KEY_{$K}_SENDER_ID"]  = (string) $p->sender_id;
            $pairs["FIREBASE_KEY_{$K}_TOPIC"]      = (string) $p->topic;

            $relPath = $this->writeCredentialsFile($p);
            if ($relPath) {
                $pairs["FIREBASE_KEY_{$K}_CREDENTIALS"] = $relPath;
            }

            if ($p->is_default) {
                $default = $p->key;
            }
        }

        if ($default !== null) {
            $pairs['FIREBASE_DEFAULT_PROJECT'] = $default;
        }

        return empty($pairs) ? true : EnvWriter::setMany($pairs);
    }

    /** Absolute path where a project's service-account JSON is stored. */
    protected function credentialsFilePath(string $key): string
    {
        return storage_path('app/firebase/' . $key . '.json');
    }

    /**
     * Write the project's service-account JSON to disk.
     *
     * @return string|null path relative to the project root (for .env), or null
     */
    protected function writeCredentialsFile(FirebaseProject $project): ?string
    {
        if (empty($project->credentials)) {
            return null;
        }

        $dir = storage_path('app/firebase');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0777, true);
        }

        $abs = $this->credentialsFilePath($project->key);
        File::put($abs, $project->credentials);

        // Relative, forward-slashed path so .env stays portable.
        return 'storage/app/firebase/' . $project->key . '.json';
    }

    /** Suffix appended to the success flash to report the .env write. */
    protected function envNote(bool $ok): string
    {
        return $ok
            ? ' .env updated automatically.'
            : ' (Note: could not write .env — check file permissions. DB config is saved and active.)';
    }

    /* -----------------------------------------------------------------
     | Helpers
     |-----------------------------------------------------------------*/

    /**
     * Validate the request and build the project data.
     *
     * The form has only THREE inputs — Service Account JSON,
     * google-services.json and Sender ID. Everything else is derived
     * automatically:
     *   - project_id  ← service-account JSON / google-services.json
     *   - key         ← slug of project_id (stable + unique per app)
     *   - display_name← project_id
     *   - topic       ← google-services.json package name
     *   - sender_id   ← the Sender ID field, else google-services project_number
     *
     * On create the service-account JSON is required; on edit it's optional
     * (keep the existing one if not re-uploaded).
     */
    protected function validateAndExtract(Request $request, ?FirebaseProject $existing): array
    {
        $serviceRule = $existing ? 'nullable' : 'required';

        $request->validate([
            'service_account' => [$serviceRule, 'file', 'max:512', $this->jsonExtensionRule()],
            'google_services' => ['nullable', 'file', 'max:512', $this->googleConfigRule()],
            'sender_id'       => ['nullable', 'string', 'max:100'],
        ]);

        $data = [
            'project_id'               => $existing->project_id ?? null,
            'topic'                    => $existing->topic ?? null,
            'sender_id'                => $request->filled('sender_id') ? trim($request->input('sender_id')) : ($existing->sender_id ?? null),
            'credentials'              => $existing->credentials ?? null,
            'service_account_filename' => $existing->service_account_filename ?? null,
            'google_services_filename' => $existing->google_services_filename ?? null,
        ];

        // Service-account JSON → credentials (+ project_id).
        if ($request->hasFile('service_account')) {
            $raw  = file_get_contents($request->file('service_account')->getRealPath());
            $json = json_decode($raw, true);
            if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
                abort(redirect()->back()->withInput()->withErrors([
                    'service_account' => 'That does not look like a valid Firebase service-account JSON.',
                ]));
            }
            $data['credentials'] = $raw;
            $data['service_account_filename'] = $request->file('service_account')->getClientOriginalName();
            if (!empty($json['project_id'])) {
                $data['project_id'] = $json['project_id'];
            }
        }

        // google-services.json (Android) OR GoogleService-Info.plist (iOS) →
        // project_id, sender_id, topic (package / bundle id).
        if ($request->hasFile('google_services')) {
            $file = $request->file('google_services');
            $data['google_services_filename'] = $file->getClientOriginalName();
            $content = file_get_contents($file->getRealPath());
            $ext = strtolower($file->getClientOriginalExtension()
                ?: pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));

            if ($ext === 'plist') {
                // iOS GoogleService-Info.plist — flat <key>/<value> dictionary.
                $plist = $this->parsePlist($content);
                if (!empty($plist['PROJECT_ID'])) {
                    $data['project_id'] = $plist['PROJECT_ID'];
                }
                if (empty($data['sender_id']) && !empty($plist['GCM_SENDER_ID'])) {
                    $data['sender_id'] = $plist['GCM_SENDER_ID'];
                }
                if (!empty($plist['BUNDLE_ID'])) {
                    $data['topic'] = $plist['BUNDLE_ID']; // iOS bundle id = topic
                }
            } else {
                // Android google-services.json.
                $gs = json_decode($content, true);
                if (is_array($gs)) {
                    $projInfo = $gs['project_info'] ?? [];
                    if (!empty($projInfo['project_id'])) {
                        $data['project_id'] = $projInfo['project_id'];
                    }
                    if (empty($data['sender_id']) && !empty($projInfo['project_number'])) {
                        $data['sender_id'] = $projInfo['project_number'];
                    }
                    $pkg = $gs['client'][0]['client_info']['android_client_info']['package_name'] ?? null;
                    if ($pkg) {
                        $data['topic'] = $pkg; // package = topic
                    }
                }
            }
        }

        if (empty($data['project_id'])) {
            abort(redirect()->back()->withInput()->withErrors([
                'service_account' => 'Could not read the project_id from the uploaded JSON.',
            ]));
        }

        // Auto key + display name from the project id. If the derived key is
        // already taken by another project, append a timestamp to keep it
        // unique instead of blocking the save (duplicates are allowed).
        $data['key']          = $this->uniqueKey($this->makeKey($data['project_id']), $existing ? $existing->id : null);
        $data['display_name'] = $existing ? $existing->display_name : $data['project_id'];

        return $data;
    }

    /** Derive a stable lowercase_underscore key from a Firebase project id. */
    protected function makeKey(string $projectId): string
    {
        $key = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $projectId));
        return trim($key, '_') ?: 'project';
    }

    /**
     * Return $key unchanged if it's free, otherwise append a timestamp
     * (and a counter on the rare clash) so duplicates can be added.
     */
    protected function uniqueKey(string $key, ?int $ignoreId = null): string
    {
        $taken = function (string $candidate) use ($ignoreId): bool {
            $q = FirebaseProject::where('key', $candidate);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }
            return $q->exists();
        };

        if (!$taken($key)) {
            return $key;
        }

        $candidate = $key . '_' . time();
        $i = 1;
        while ($taken($candidate)) {
            $candidate = $key . '_' . time() . '_' . $i++;
        }
        return $candidate;
    }

    /** Accept an uploaded file only if its extension is .json (mime sniffing is unreliable). */
    protected function jsonExtensionRule(): \Closure
    {
        return $this->extensionRule(['json'], 'Please upload a .json file.');
    }

    /** Accept google-services.json (Android) or GoogleService-Info.plist (iOS). */
    protected function googleConfigRule(): \Closure
    {
        return $this->extensionRule(['json', 'plist'], 'Please upload google-services.json (Android) or GoogleService-Info.plist (iOS).');
    }

    /** Generic extension allow-list validation rule. */
    protected function extensionRule(array $allowed, string $message): \Closure
    {
        return function ($attribute, $value, $fail) use ($allowed, $message) {
            if (!$value instanceof \Illuminate\Http\UploadedFile) {
                return;
            }
            $ext = strtolower(
                $value->getClientOriginalExtension()
                    ?: pathinfo($value->getClientOriginalName(), PATHINFO_EXTENSION)
            );
            if (!in_array($ext, $allowed, true)) {
                $fail($message);
            }
        };
    }

    /**
     * Parse a flat Apple .plist (GoogleService-Info.plist) into an
     * associative array of its top-level <key>/<value> pairs.
     */
    protected function parsePlist(string $content): array
    {
        $out = [];
        $prev = libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_use_internal_errors($prev);

        if ($xml === false || !isset($xml->dict)) {
            return $out;
        }

        // Children alternate: <key>NAME</key> followed by its value node.
        $nodes = $xml->dict->children();
        $count = count($nodes);
        for ($i = 0; $i < $count - 1; $i++) {
            if ($nodes[$i]->getName() === 'key') {
                $key   = (string) $nodes[$i];
                $value = $nodes[$i + 1];
                $vName = $value->getName();
                if ($vName === 'true' || $vName === 'false') {
                    $out[$key] = $vName === 'true';
                } else {
                    $out[$key] = trim((string) $value);
                }
                $i++; // consume the value node
            }
        }
        return $out;
    }

    /** Save the project and keep the single-default invariant. */
    protected function persist(FirebaseProject $project, array $data, bool $makeDefault): void
    {
        DB::transaction(function () use ($project, $data, $makeDefault) {
            $project->fill($data);

            // First project ever becomes default automatically.
            if (!FirebaseProject::where('id', '!=', $project->id ?? 0)->exists()) {
                $makeDefault = true;
            }

            $project->is_default = $makeDefault;
            $project->is_active  = true;
            $project->save();

            if ($makeDefault) {
                FirebaseProject::where('id', '!=', $project->id)->update(['is_default' => false]);
            }
        });
    }
}
