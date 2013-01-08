<?php
namespace Slik\Uncia;

function confirm($str)
{
	$str = trim($str);
	if (substr($str, -1) !== '?') {
		$str = rtrim($str, '.') . '.';
		$str .= ' Continue?';
	}
	echo $str . ' [Yn] ';
	$char = stdin('character');
	if ($char === "\n") {
		return true;
	}
	echo "\n";
	return strtolower($char) === 'y';
}

function Out()
{
	return Output::create(func_get_args());
}

function prompt($prefix = null)
{
	if ($prefix) {
		$prefix = trim($prefix);
		$prefix = rtrim($prefix, ':');
		echo $prefix . ': ';
	}
	return stdin('blocking');
}

function stderr()
{
	return Output::create(func_get_args())->stderr();
}

/**
 * Read a line from stdin
 *
 * There are two special modes:
 *  blocking - read will be blocking
 *  character - read will be blocking, only read 1 byte
 */
function stdin($mode = null)
{
	if ($mode === 'character') {
		stream_set_blocking(STDIN, true);
		$term = exec('stty -g');
		exec('stty -icanon');
		$result = fread(STDIN, 1);
		exec("stty '$term'");
		return $result;
	}
	stream_set_blocking(STDIN, $mode === 'blocking');
	return trim(fgets(STDIN));
}

function stdout()
{
	return Output::create(func_get_args())->stdout();
}
