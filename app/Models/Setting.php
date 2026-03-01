<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
    ];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever('setting_' . $key, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value, array $attributes = [])
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            array_merge(['value' => $value], $attributes)
        );

        Cache::forget('setting_' . $key);

        return $setting;
    }

    public static function getByGroup(string $group)
    {
        return self::where('group', $group)->get();
    }

    public static function clearCache()
    {
        $settings = self::all();
        foreach ($settings as $setting) {
            Cache::forget('setting_' . $setting->key);
        }
    }
}
