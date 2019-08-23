<?php
namespace

{
    foreach ([
        'PHPUnit_Framework_TestCase' => 'PHPUnit\Framework\TestCase',
    ] as $class => $namespacedClass) {
        if (! class_exists($class) && class_exists($namespacedClass)) {
            class_alias($namespacedClass, $class);
        }
    }

    include_once __DIR__ . '/vendor/composer/autoload_real.php';
}
