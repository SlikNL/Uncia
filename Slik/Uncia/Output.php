<?php
namespace Slik\Uncia;

class Output
{
	/**
	 * API
	 */

	/**
	 * @param array $args An array of items to show
	 */
	public function __construct($args)
	{
		$this->args = $args;
	}

	public function stderr()
	{
		file_put_contents('php://stderr', $this->toString(STDERR));
	}

	public function stdout()
	{
		echo $this->toString(STDOUT);
	}

	public function underline($char = '-')
	{
		$this->underline = $char;
		return $this;
	}

	public static function create($args)
	{
		return new self($args);
	}


	/**
	 * Implementation
	 */

	private $args;
	private $underline;

	private function format($args)
	{
		if (!is_array($args)) {
			$args = [$args];
		}

		// Shortcut
		if (empty($args)) {
			return "\n";
		}

		// Stringify
		$args = array_map('\Slik\Uncia\str', $args);

		// Add whitespace
		$args = array_map(function ($i) {
			return preg_replace('/(?<!\s)$/', ' ', $i);
		}, $args);

		// Add a newline
		$str = implode('', $args);
		if (substr($str, -1) !== "\n") {
			$str .= "\n";
		}

		return $str;
	}

	private function toString($fd)
	{
		$result = $this->format($this->args);

		if ($this->underline) {
			$result.= str_repeat(
				$this->underline,
				ceil(
					strlen(trim($result))
					/
					strlen($this->underline)
				)
			)."\n";
		}

		// Restore colors
		if (strpos($result, "\033") !== false) {
			$result = substr($result, 0, -1) . ttycolor('restore') . substr($result, -1);
		}

		// Remove colors
		if (!posix_isatty($fd)) {
			$result = preg_replace('/\\033\\[[\\d;]{1,4}m/', '', $result);
		}

		return $result;
	}
}
