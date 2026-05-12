<?php

namespace Vormia\ATURankSEO;

class ATURankSEO
{
    public const VERSION = '1.3.1';

    /**
     * Absolute path to the package root (directory containing composer.json).
     */
    public static function basePath(string $suffix = ''): string
    {
        $base = dirname(__DIR__);

        return $suffix !== '' ? $base.DIRECTORY_SEPARATOR.ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $suffix), DIRECTORY_SEPARATOR) : $base;
    }

    /**
     * @deprecated Use basePath('src/stubs/reference') for documentation snippets.
     */
    public static function stubsPath(string $suffix = ''): string
    {
        $base = __DIR__.'/stubs';

        return $suffix !== '' ? $base.'/'.ltrim($suffix, '/') : $base;
    }
}
