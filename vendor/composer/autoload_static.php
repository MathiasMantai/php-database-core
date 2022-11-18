<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita1c418d0f9c2c863fc106d2a289f5179
{
    public static $prefixLengthsPsr4 = array (
        'D' => 
        array (
            'DbCore\\' => 7,
        ),
        'C' => 
        array (
            'Config\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'DbCore\\' => 
        array (
            0 => __DIR__ . '/../..' . '/class',
        ),
        'Config\\' => 
        array (
            0 => __DIR__ . '/../..' . '/config',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'DbCore\\Csrf' => __DIR__ . '/../..' . '/class/Csrf.php',
        'DbCore\\DbCore' => __DIR__ . '/../..' . '/class/DbCore.php',
        'DbCore\\ErrorLog' => __DIR__ . '/../..' . '/class/ErrorLog.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita1c418d0f9c2c863fc106d2a289f5179::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita1c418d0f9c2c863fc106d2a289f5179::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita1c418d0f9c2c863fc106d2a289f5179::$classMap;

        }, null, ClassLoader::class);
    }
}