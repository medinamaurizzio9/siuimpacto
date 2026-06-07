<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    public const LOGIN_BACKGROUND = 'login_background';

    protected $fillable = ['key', 'value'];
}
