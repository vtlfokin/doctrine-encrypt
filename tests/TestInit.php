<?php
//From https://github.com/doctrine/doctrine2/blob/master/tests/Doctrine/Tests/TestInit.php
/*
* This file bootstraps the test environment.
*/
namespace Doctrine\Tests;

use Doctrine\Common\Annotations\AnnotationRegistry;

error_reporting(E_ALL | E_STRICT);

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    // dependencies were installed via composer - this is the main project
    $classLoader = require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    // installed as a dependency in `vendor`
    $classLoader = require __DIR__ . '/../../../autoload.php';
} else {
    throw new \Exception('Can\'t find autoload.php. Did you install dependencies via composer?');
}

/* @var $classLoader \Composer\Autoload\ClassLoader */
$classLoader->add('DoctrineEncrypt\\Tests\\', __DIR__);

AnnotationRegistry::registerLoader(array($classLoader, 'loadClass'));
unset($classLoader);