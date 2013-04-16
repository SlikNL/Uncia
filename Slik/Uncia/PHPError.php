<?php
namespace Slik\Uncia;

class PHPError extends \Exception
{
	public function __construct($msg, $file, $line, $context)
	{
		$this->file = $file;
		$this->line = $line;
		$this->context = $context;
		parent::__construct($msg);
	}

	public function getContext()
	{
		return $this->context;
	}

	private $context;
}
