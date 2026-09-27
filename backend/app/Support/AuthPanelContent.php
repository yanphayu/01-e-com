<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Collection;

class AuthPanelContent
{
    /**
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return config('authpanel');
    }

    /**
     * @return array<string, string>
     */
    public static function resolve(): array
    {
        $defaults = self::defaults();
        $stored = Setting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key');

        $content = [];

        foreach ($defaults as $key => $default) {
            $content[$key] = self::pick($stored, $key, $default);
        }

        return $content;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public static function persist(array $input): void
    {
        foreach (array_keys(self::defaults()) as $key) {
            if (! array_key_exists($key, $input)) {
                continue;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => self::clean($input[$key])],
            );
        }
    }

    public static function forget(): void
    {
        Setting::query()->whereIn('key', array_keys(self::defaults()))->delete();
    }

    private static function pick(Collection $stored, string $key, string $default): string
    {
        $value = self::clean($stored->get($key));

        return $value !== '' ? $value : $default;
    }

    private static function clean(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
