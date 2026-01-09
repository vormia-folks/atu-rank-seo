<?php

namespace Vormia\ATURankSEO;

class ATURankSEO
{
    public const VERSION = '1.2.0';

    /**
     * Absolute path to the package stubs.
     */
    public static function stubsPath(string $suffix = ''): string
    {
        $base = __DIR__ . '/stubs';

        return $suffix ? $base . '/' . ltrim($suffix, '/') : $base;
    }
}
