<?php
namespace Slik\Uncia;

function run($cmd, $values = null, $options = null)
{
	if (strpos($cmd, '?') === false && is_null($options)) {
		$options = $values;
		$values = null;
	}

	$cmd = _run_placeholders($cmd, $values);

	$options = (object) array_merge(array(
		'stdin' => null,
	), $options ?: array());

	$desc = array(
		0 => array('pipe', 'r'),
		1 => array('pipe', 'w'),
		2 => array('pipe', 'w'),
	);

	$process = proc_open($cmd, $desc, $pipes);

	if ($options->stdin) {
		fwrite($pipes[0], $options->stdin);
	}
	fclose($pipes[0]);

	$stdout = stream_get_contents($pipes[1]);
	fclose($pipes[1]);

	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);

	$code = proc_close($process);

	if ($code > 0) {
		throw new Exception\RunError($stderr);
	}

	return $stdout;
};

function _run_placeholders($cmd, $values)
{
	$values = (array) $values ?: array();

	if (preg_match_all('/(?<= )\\?($| )/', $cmd) !== count($values)) {
		throw new SyntaxError('Parameter count does not match values count');
	}

	foreach ($values as $v) {
		$v = escapeshellarg($v);
		$v = str_replace('\\', '\\\\', str_replace('?', '\\?', $v));
		$cmd = preg_replace('/(?<= )\\?($| )/', $v.'\1', $cmd, 1);
	}

	$cmd = str_replace('\\?', '?', $cmd);

	return $cmd;
}