<?php

namespace Vormia\ATURankSEO\Models;

use Illuminate\Database\Eloquent\Model;

class RankSeoSettings extends Model
{
    protected $table = 'atu_rankseo_settings';

    public $timestamps = false;

    protected $fillable = [
        'is_enabled',
        'global_title',
        'global_description',
        'global_keywords',
        'dynamic_variables',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'dynamic_variables' => 'array',
    ];

    /**
     * Get the singleton settings instance.
     * Creates one if it doesn't exist.
     */
    public static function getInstance(): self
    {
        $settings = static::first();

        if (!$settings) {
            $settings = static::create([
                'is_enabled' => true,
                'global_title' => null,
                'global_description' => null,
                'global_keywords' => null,
                'dynamic_variables' => [],
            ]);
        }

        return $settings;
    }

    /**
     * Update settings.
     */
    public function updateSettings(array $data): bool
    {
        return $this->update($data);
    }

    /**
     * Get a dynamic variable value.
     */
    public function getVariable(string $key, $default = null)
    {
        $variables = $this->dynamic_variables ?? [];

        return $variables[$key] ?? $default;
    }

    /**
     * Set a dynamic variable value.
     */
    public function setVariable(string $key, $value): bool
    {
        $variables = $this->dynamic_variables ?? [];
        $variables[$key] = $value;

        return $this->update(['dynamic_variables' => $variables]);
    }
}
