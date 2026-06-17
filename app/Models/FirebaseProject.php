<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirebaseProject extends Model
{
    protected $table = 'firebase_projects';

    protected $fillable = [
        'key',
        'display_name',
        'project_id',
        'sender_id',
        'topic',
        'credentials',
        'service_account_filename',
        'google_services_filename',
        'is_default',
        'is_active',
    ];

    /** Service-account email parsed from the stored credentials, if any. */
    public function clientEmail(): ?string
    {
        $creds = $this->credentialsArray();
        return $creds['client_email'] ?? null;
    }

    protected $casts = [
        'is_default' => 'boolean',
        'is_active'  => 'boolean',
    ];

    /** Decoded service-account JSON, or null when not set / invalid. */
    public function credentialsArray(): ?array
    {
        if (empty($this->credentials)) {
            return null;
        }
        $json = json_decode($this->credentials, true);
        return is_array($json) ? $json : null;
    }

    /** Whether this project has a usable service account configured. */
    public function isUsable(): bool
    {
        $creds = $this->credentialsArray();
        return $creds && !empty($creds['client_email']) && !empty($creds['private_key']);
    }
}
