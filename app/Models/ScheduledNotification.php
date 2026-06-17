<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledNotification extends Model
{
    protected $table = 'scheduled_notifications';

    protected $fillable = [
        'module',
        'type',
        'category_id',
        'title',
        'description',
        'image_url',
        'click_action',
        'screen',
        'extra_data',
        'target_project_ids',
        'scheduled_at',
        'sent_at',
        'status',
        'sent_count',
        'failed_count',
        'last_error',
    ];

    protected $casts = [
        'extra_data'         => 'array',
        'target_project_ids' => 'array',
        'scheduled_at'       => 'datetime',
        'sent_at'            => 'datetime',
    ];
}
