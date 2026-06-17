<?php

namespace App\Services;

use App\Models\FirebaseProject;
use App\Models\NotificationLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging service using the FCM HTTP v1 API.
 *
 * Authenticates with a Google service-account JSON via OAuth2 (JWT bearer
 * flow) and sends notifications to topics. No external Firebase package
 * required and no per-device tokens stored — every install of an app
 * subscribes to that app's topic, so we broadcast by topic.
 *
 * This admin panel serves several apps, so the service is built per
 * Firebase project via FcmService::forProject($project).
 */
class FcmService
{
    /** @var array decoded service-account JSON */
    protected $credentials;

    /** @var string Firebase project id */
    protected $projectId;

    /** @var int|null id of the FirebaseProject row, for logging */
    protected $firebaseProjectId;

    /** @var Client */
    protected $http;

    public function __construct(array $credentials, string $projectId, ?int $firebaseProjectId = null)
    {
        if (empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new \RuntimeException('Invalid Firebase service-account credentials.');
        }

        $this->credentials       = $credentials;
        $this->projectId         = $projectId ?: (string) ($credentials['project_id'] ?? '');
        $this->firebaseProjectId = $firebaseProjectId;

        if (empty($this->projectId)) {
            throw new \RuntimeException('Firebase project id is missing.');
        }

        $this->http = new Client([
            'timeout'         => 15,
            'connect_timeout' => 10,
        ]);
    }

    /** Build a service instance from a FirebaseProject model. */
    public static function forProject(FirebaseProject $project): self
    {
        $creds = $project->credentialsArray();
        if (!$creds) {
            throw new \RuntimeException("Firebase project '{$project->key}' has no valid service-account JSON.");
        }
        return new self($creds, (string) ($project->project_id ?: ($creds['project_id'] ?? '')), $project->id);
    }

    /**
     * Send a notification to a topic (e.g. the app's package name).
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = [], ?string $imageUrl = null, array $meta = []): array
    {
        $clickAction = $data['click_action'] ?? null;
        return $this->send([
            'topic'        => $topic,
            'notification' => $this->buildNotification($title, $body, $imageUrl),
            'data'         => $this->stringifyData($data),
            'android'      => $this->androidConfig($imageUrl, $clickAction),
            'apns'         => $this->apnsConfig($imageUrl, $clickAction),
        ], 'topic', $topic, $title, $body, $imageUrl, $data, $meta);
    }

    /**
     * Send a notification to a single device token (kept for completeness).
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [], ?string $imageUrl = null, array $meta = []): array
    {
        $clickAction = $data['click_action'] ?? null;
        return $this->send([
            'token'        => $token,
            'notification' => $this->buildNotification($title, $body, $imageUrl),
            'data'         => $this->stringifyData($data),
            'android'      => $this->androidConfig($imageUrl, $clickAction),
            'apns'         => $this->apnsConfig($imageUrl, $clickAction),
        ], 'token', $token, $title, $body, $imageUrl, $data, $meta);
    }

    /**
     * Look up a device token's registration details via the Instance ID
     * Info API: which app (package), which project (authorizedEntity =
     * sender id) and which topics it's subscribed to. Pure diagnostics.
     */
    public function getTokenInfo(string $token): array
    {
        try {
            $accessToken = $this->getAccessToken();
            $resp = $this->http->get('https://iid.googleapis.com/iid/info/' . rawurlencode($token) . '?details=true', [
                'headers' => [
                    'Authorization'     => 'Bearer ' . $accessToken,
                    'access_token_auth' => 'true',
                ],
                'http_errors' => false,
            ]);
            $status = $resp->getStatusCode();
            $body   = json_decode((string) $resp->getBody(), true);

            if ($status >= 200 && $status < 300 && is_array($body)) {
                return ['success' => true, 'info' => $body];
            }
            return ['success' => false, 'error' => $body['error'] ?? ('HTTP ' . $status), 'raw' => $body];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /* -----------------------------------------------------------------
     | Internals
     |-----------------------------------------------------------------*/

    protected function send(array $messagePayload, string $targetType, string $target, string $title, string $body, ?string $imageUrl, array $data, array $meta): array
    {
        // FCM requires `data` to be a JSON object. An empty PHP array encodes
        // as a JSON list ([]) and is rejected ("Cannot bind a list to map"),
        // so drop the key entirely when there's nothing to send.
        if (isset($messagePayload['data']) && empty($messagePayload['data'])) {
            unset($messagePayload['data']);
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $logRow = NotificationLog::create([
            'scheduled_notification_id' => $meta['scheduled_notification_id'] ?? null,
            'firebase_project_id'       => $this->firebaseProjectId,
            'module'      => $meta['module'] ?? null,
            'event'       => $meta['event'] ?? null,
            'target_type' => $targetType,
            'target'      => $target,
            'title'       => $title,
            'body'        => $body,
            'image_url'   => $imageUrl,
            'data'        => $data,
            'success'     => 0,
        ]);

        try {
            $accessToken = $this->getAccessToken();

            $requestBody = ['message' => $messagePayload];

            $response = $this->http->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json; UTF-8',
                ],
                'json'        => $requestBody,
                'http_errors' => false,
            ]);

            $status  = $response->getStatusCode();
            $resBody = (string) $response->getBody();
            $resJson = json_decode($resBody, true);
            $success = $status >= 200 && $status < 300 && isset($resJson['name']);

            $logRow->success  = $success ? 1 : 0;
            $logRow->response = mb_substr($resBody, 0, 4000);
            $logRow->save();

            if (!$success) {
                $errMsg = $resJson['error']['message'] ?? ('HTTP ' . $status);
                Log::warning('FCM send failed', ['target_type' => $targetType, 'target' => $target, 'response' => $resBody]);
                return ['success' => false, 'error' => $errMsg, 'response' => $resJson];
            }

            return ['success' => true, 'name' => $resJson['name'], 'response' => $resJson];

        } catch (\Throwable $e) {
            $logRow->success  = 0;
            $logRow->response = mb_substr('EXCEPTION: ' . $e->getMessage(), 0, 4000);
            $logRow->save();
            Log::error('FCM exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function buildNotification(string $title, string $body, ?string $imageUrl = null): array
    {
        $n = ['title' => $title, 'body' => $body];
        if ($imageUrl) {
            $n['image'] = $imageUrl;
        }
        return $n;
    }

    protected function androidConfig(?string $imageUrl = null, ?string $clickAction = null): array
    {
        $notif = ['sound' => 'default'];

        $channelId = (string) config('services.firebase.android_channel_id');
        if ($channelId !== '') {
            $notif['channel_id'] = $channelId;
        }

        $smallIcon = (string) config('services.firebase.android_small_icon');
        if ($smallIcon !== '') {
            $notif['icon'] = $smallIcon;
        }

        $iconColor = (string) config('services.firebase.android_icon_color');
        if ($iconColor !== '') {
            $notif['color'] = $iconColor;
        }

        if ($imageUrl) {
            $notif['image'] = $imageUrl;
        }

        return [
            'priority'     => 'HIGH',
            'notification' => $notif,
        ];
    }

    protected function apnsConfig(?string $imageUrl = null, ?string $clickAction = null): array
    {
        $cfg = [
            'payload' => [
                'aps' => [
                    'sound'           => 'default',
                    'badge'           => 1,
                    'mutable-content' => 1,
                    'category'        => $clickAction ?: 'DEFAULT',
                ],
            ],
        ];
        if ($imageUrl) {
            $cfg['fcm_options'] = ['image' => $imageUrl];
        }
        return $cfg;
    }

    /** FCM data payload requires string values. */
    protected function stringifyData(array $data): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $out[(string) $k] = json_encode($v);
            } else {
                $out[(string) $k] = is_null($v) ? '' : (string) $v;
            }
        }
        return $out;
    }

    /* -----------------------------------------------------------------
     | OAuth2 access token (JWT bearer flow)
     |-----------------------------------------------------------------*/

    protected function getAccessToken(): string
    {
        $cacheKey = 'fcm_access_token_' . md5($this->credentials['client_email']);
        $cached   = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        $now = time();
        $jwt = $this->createSignedJwt([
            'iss'   => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]);

        $resp = $this->http->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ],
            'http_errors' => false,
        ]);

        $body = json_decode((string) $resp->getBody(), true);
        if (empty($body['access_token'])) {
            throw new \RuntimeException('Could not obtain Google OAuth2 access token: ' . json_encode($body));
        }

        $ttl = max(60, (int) ($body['expires_in'] ?? 3600) - 60);
        Cache::put($cacheKey, $body['access_token'], $ttl);

        return $body['access_token'];
    }

    protected function createSignedJwt(array $claims): string
    {
        $header   = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];

        $signingInput = implode('.', $segments);
        $signature    = '';
        $privateKey   = openssl_pkey_get_private($this->credentials['private_key']);
        if (!$privateKey) {
            throw new \RuntimeException('Invalid private_key in Firebase service-account JSON.');
        }
        openssl_sign($signingInput, $signature, $privateKey, 'SHA256');

        if (PHP_VERSION_ID < 80000) {
            openssl_free_key($privateKey);
        }

        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
