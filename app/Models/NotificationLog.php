<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'notification_logs';

    protected $fillable = [
        'scheduled_notification_id',
        'firebase_project_id',
        'module',
        'event',
        'target_type',
        'target',
        'title',
        'body',
        'image_url',
        'data',
        'success',
        'response',
    ];

    protected $casts = [
        'data'    => 'array',
        'success' => 'boolean',
    ];

    public function firebaseProject()
    {
        return $this->belongsTo(FirebaseProject::class, 'firebase_project_id');
    }
}
