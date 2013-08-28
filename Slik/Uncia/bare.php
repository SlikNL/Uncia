<?php
namespace Slik\Uncia;

function aliases()
{
	require_once __DIR__ .'/aliases.php';
}

function errors()
{
	if (!isset($_SERVER['UNCIA_DEBUG'])) {

		// Workaround PHP bug 42098
		// https://bugs.php.net/bug.php?id=42098
		class_exists('\\Slik\\Uncia\\PHPError');

		set_error_handler(
			function ($num, $message, $file = null, $line = null, $context = null) {
				if (!(error_reporting() & $num)) {
					return;
				}

				throw new PHPError($message, $num, 0, $file, $line);
			}
		);

		set_exception_handler(
			function ($exc) {
				if ($exc instanceof Exception\Abort) {
					exit($exc->getCode());
				}
				$str = ttycolor('brown').'Error:'
					.' '.trim($exc->getMessage() ?: get_class($exc));
				if ($exc->getFile()) {
					$str .= ' in ' . basename($exc->getFile());
					if ($exc->getLine()) {
						$str .= ':'.$exc->getLine();
					}
				}
				stderr($str);

				if (isset($_SERVER['DEBUG'])) {
					stderr('Traceback:');
					foreach ($exc->getTrace() as $frame) {
						if (isset($frame['file'])) {
							$str = ' - ' . basename($frame['file']);
							if ($frame['line']) {
								$str .= ':'.$frame['line'];
							}
							stderr($str);
						}
					}
				}
				exit(1);
			}
		);

		error_reporting(E_ALL | E_STRICT);
	}
}

function setErrorHandler(callable $handler)
{
	$previous = set_error_handler(
		function ($num, $message, $file = null, $line = null, $context = null) use ($handler, &$previous) {

			if (!$previous) {
				$previous = function () {
					return false;
				};
			}

			return $handler($num, $message, $file, $line, $context)
				|| $previous($num, $message, $file, $line, $context);
		}
	);
}
