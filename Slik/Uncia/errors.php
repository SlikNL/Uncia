<?php
namespace Slik\Uncia;

if (!isset($_SERVER['UNCIA_DEBUG'])) {
	set_error_handler(
		function ($no, $message, $file = null, $line = null, $context = null) {
			throw new PHPError($message, $file, $line);
		}
	);

	set_exception_handler(
		function ($e) {
			if ($e instanceof Exception\Abort) {
				exit($e->getCode());
			}
			$str = 'Error: '.trim($e->getMessage() ?: get_class($e));
			if ($e->getFile()) {
				$str .= ' in ' . basename($e->getFile());
				if ($e->getLine()) {
					$str .= ':'.$e->getLine();
				}
			}
			$str .= "\n";
			stderr($str);
		}
	);

	error_reporting(E_ALL | E_STRICT);
}
