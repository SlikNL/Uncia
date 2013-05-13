<?php
namespace Slik\Uncia;

function str($anything)
{
	$i = $anything;
	if (is_string($i)) {
		return $i;
	}
	if (is_array($i)) {
		$max = 7;
		if (count($i) > $max) {
			return preg_replace(
				'/\]$/',
				', ...('.(count($i) - $max).' hidden)]',
				str(array_slice($i, 0, $max))
			);
		}
		if (array_values($i) == $i) {
			$items = array_map('\Slik\Uncia\str', $i);
		} else {
			$items = array();
			foreach ($i as $k => $v) {
				$items[] = str($k) . '=>' . str($v);
			}
		}
		return '['.implode(', ', $items).']';
	}
	if (is_float($i)) {
		return sprintf(abs($i) > 1 ? '≈%.3f' : '≈%.4f', $i);
	}
	if (is_object($i)) {
		if ($i instanceof \stdClass) {
			return preg_replace('/^\[(.+)\]$/', '{stdClass \1}', str((array) $i));
		}
		if (method_exists($i, '__toString')) {
			$s = trim((string) $i);
			if ($s && strpos($s, "\n") === false) {
				return $s;
			}
		}
		$s = array(get_class($i));
		if (method_exists($i, 'getId')) {
			$s[] = '#'.$i->getId();
		}
		if (method_exists($i, 'getName')) {
			$s[] = $i->getName();
		}
		if (method_exists($i, 'getTitle')) {
			$s[] = $i->getTitle();
		}
		if (method_exists($i, 'getMessage')) {
			$s[] = $i->getMessage();
		}
		return '{'.implode(' ', array_slice($s, 0, 2)).'}';
	}
	return var_export($i, true);
}
