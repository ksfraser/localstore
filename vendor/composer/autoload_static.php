<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0b2a049944963e0f97087d039a00ac42
{
    public static $prefixLengthsPsr4 = array (
        'k' => 
        array (
            'ksfraser\\origin\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ksfraser\\origin\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit0b2a049944963e0f97087d039a00ac42::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit0b2a049944963e0f97087d039a00ac42::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
