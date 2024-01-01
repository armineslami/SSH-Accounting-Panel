<?php

namespace App\Repositories;

use App\Interfaces\SettingRepositoryInterface;
use App\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    public static function first(): Setting
    {
        return Setting::first();
    }

    public static function update(Setting $old_setting, array $new_setting): bool
    {
        return $old_setting->fill($new_setting)->save();
    }
}
