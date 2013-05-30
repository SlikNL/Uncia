<?php
namespace Slik\Uncia;
use Slik\Uncia\Exception\SyntaxError;

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

function preg_all($pattern, $subject)
{
	try {
		$count = preg_match_all($pattern, $subject, $matches, PREG_SET_ORDER);
	} catch (PHPError $e) {
		throw new SyntaxError($e->getMessage());
	}
	if (!$count) {
		return [];
	}
	if (count($matches[0]) === 1) {
		$f = function ($match) {
			return $match[0];
		};
	} elseif (count($matches[0]) === 2) {
		$f = function ($match) {
			return $match[1];
		};
	} else {
		$f = function ($match) {
			return array_slice($match, 1);
		};
	}
	return array_map($f, $matches);
}
