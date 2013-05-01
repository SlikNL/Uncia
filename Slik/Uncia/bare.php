<?php
namespace Slik\Uncia;

function aliases()
{
	require_once __DIR__ .'/aliases.php';
}

function errors()
{
	if (!isset($_SERVER['UNCIA_DEBUG'])) {
		set_error_handler(
			function ($no, $message, $file = null, $line = null, $context = null) {
				$minorError = (bool) ($no & (E_STRICT | E_DEPRECATED));
				if (strpos($file, '/share/pear/') !== false && $minorError) {
					return;
				}
				if (!(error_reporting() & $no)) {
					return;
				}
				throw new PHPError($message, $file, $line, $context);
			}
		);

		set_exception_handler(
			function ($e) {
				if ($e instanceof Exception\Abort) {
					exit($e->getCode());
				}
				$str = ttycolor('brown').'Error:'
					.' '.trim($e->getMessage() ?: get_class($e));
				if ($e->getFile()) {
					$str .= ' in ' . basename($e->getFile());
					if ($e->getLine()) {
						$str .= ':'.$e->getLine();
					}
				}
				stderr($str);

				if (isset($_SERVER['DEBUG'])) {
					stderr('Traceback:');
					foreach ($e->getTrace() as $frame) {
						if (isset($frame['file'])) {
							$str = ' - ' . basename($frame['file']);
							if ($frame['line']) {
								$str .= ':'.$frame['line'];
							}
							stderr($str);
						}
					}
				}
			}
		);

		error_reporting(E_ALL | E_STRICT);
	}
}

function setErrorHandler(callable $handler)
{
	$previous = set_error_handler(
		function ($no, $message, $file = null, $line = null, $context = null) use ($handler, &$previous) {

			if (!$previous) {
				$previous = function () {
					return false;
				};
			}

			return $handler($no, $message, $file, $line, $context)
				|| $previous($no, $message, $file, $line, $context);
		}
	);
}
