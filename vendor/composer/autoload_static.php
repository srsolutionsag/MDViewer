<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit20452b739bfc4e5bdd6fb4068763612d
{
    public static $prefixLengthsPsr4 = array (
        'c' => 
        array (
            'cebe\\markdown\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'cebe\\markdown\\' => 
        array (
            0 => __DIR__ . '/..' . '/cebe/markdown',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit20452b739bfc4e5bdd6fb4068763612d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit20452b739bfc4e5bdd6fb4068763612d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
