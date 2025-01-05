<?php

use \Composer\Autoload\ClassLoader;

date_default_timezone_set('UTC');

// Enable Composer autoloader
/** @var ClassLoader $autoloader */
$autoloader = require dirname(__DIR__) . '/vendor/autoload.php';

// Register test classes
$autoloader->addPsr4('Laravel\\PricingPlans\\Tests\\', __DIR__);
