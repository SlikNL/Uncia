<?php
namespace Slik\Uncia;

class PHPError extends \Exception
{
	public function __construct($msg, $file, $line)
	{
		$this->file = $file;
		$this->line = $line;
		parent::__construct($msg);
	}
}
