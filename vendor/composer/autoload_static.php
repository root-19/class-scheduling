<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit95e0a8ea699632350c02d92dd79d3dfc
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit95e0a8ea699632350c02d92dd79d3dfc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit95e0a8ea699632350c02d92dd79d3dfc::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit95e0a8ea699632350c02d92dd79d3dfc::$classMap;

        }, null, ClassLoader::class);
    }
}
