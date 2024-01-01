<?php

namespace App\Interfaces;

use App\Models\Setting;

interface SettingRepositoryInterface
{
    public static function first(): Setting;
    public static function update(Setting $old_setting, array $new_setting): bool;
}
