<?php
require_once __DIR__ . '/Slik/Uncia/bare.php';
require_once __DIR__ . '/Slik/Uncia/io.php';
require_once __DIR__ . '/Slik/Uncia/preg.php';
require_once __DIR__ . '/Slik/Uncia/run.php';
require_once __DIR__ . '/Slik/Uncia/str.php';

if (version_compare(PHP_VERSION, '5.3', '<')) {
	stderr('Uncia requires PHP 5.3.');
	exit(1);
}

spl_autoload_register(
	function ($name) {
		if (!preg_match('/^Slik\\\\Uncia\\\\/', $name)) {
			return false;
		}
		$path = __DIR__ . '/' . str_replace('\\', '/', $name) . '.php';
		require $path;
	}
);
