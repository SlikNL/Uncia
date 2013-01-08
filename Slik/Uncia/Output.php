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
		file_put_contents('php://stderr', $this->toString());
	}

	public function stdout()
	{
		echo $this->toString();
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

	private function toString()
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

		return $result;
	}
}
