<?php
namespace Slik\Uncia\Exception;

class Abort extends \Slik\Uncia\Exception
{
	public function __construct($code, $msg = '')
	{
		parent::__construct($msg, $code);
	}
}
