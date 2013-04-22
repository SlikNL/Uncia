<?php
namespace Slik\Uncia;

function preg($pattern, $subject)
{
	try {
		$success = preg_match($pattern, $subject, $matches);
	} catch (PHPError $e) {
		throw new SyntaxError($e->getMessage());
	}
	if (!$success) {
		throw new Exception\NotFound('Pattern '.$pattern.' did not match');
	}
	if (count($matches) === 1) {
		return $matches[0];
	}
	unset($matches[0]);
	if (count($matches) === 1) {
		return $matches[1];
	}
	return array_values($matches);
}
