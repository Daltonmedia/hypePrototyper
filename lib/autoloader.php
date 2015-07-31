<?php

if (!is_callable('hypeApps')) {
	throw new Exception("hypePrototyper requires hypeApps");
}

$path = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR;

if (!file_exists("{$path}vendor/autoload.php")) {
	throw new Exception('hypePrototyper can not resolve composer dependencies. Run composer install');
}

require_once "{$path}vendor/autoload.php";

/**
 * Plugin container
 * @return \hypeJunction\Prototyper\Plugin
 */
function hypePrototyper() {
	return \hypeJunction\Prototyper\Plugin::factory();
}