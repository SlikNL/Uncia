<?php
namespace Slik\Uncia;

class Exception extends \Exception
{
	public function __construct($message='', $code=0)
	{
		$trace = debug_backtrace();

		$level = 1;

		if (!isset($trace[$level]['file'])
			&& ($trace[$level+1]['function'] === 'call_user_func_array')
			&& ($trace[$level+1]['args'][0] === '\\Slik\\Uncia\\run')
		) {
			$level += 3;
		}

		while (isset($trace[$level+1]) && (
			$this->frameIsLocalClass($trace[$level])
			|| $this->frameIsLocalFunction($trace[$level])
		)) {
			$level += 1;
		}

		$info = $trace[$level];

		$this->file = isset($info['file']) ? $info['file'] : 'anonymous';
		$this->line = isset($info['line']) ? $info['line'] : 'unknown';

		parent::__construct($message, $code);
	}

	private function frameIsLocalClass($frame)
	{
		return isset($frame['args'][0][0])
			&& is_object($frame['args'][0][0])
			&& strpos(get_class($frame['args'][0][0]), 'Slik\\Uncia') !== false;
	}

	private function frameIsLocalFunction($frame)
	{
		return isset($frame['function'])
			&& strpos($frame['function'], 'Slik\\Uncia') !== false;
	}
}
