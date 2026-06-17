<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $table = 'notification_settings';

    protected $fillable = [
        'default_delay_minutes',
    ];

    /** Fetch the singleton settings row, creating it with defaults if missing. */
    public static function current(): self
    {
        return static::first() ?: static::create(['default_delay_minutes' => 3]);
    }
}
