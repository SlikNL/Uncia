<?php
namespace Slik\Uncia;

require_once __DIR__ . '/Exception.php';

class Args
{
	public $description;

	public function __construct($description)
	{
		$this->description = $description;
		$this->bool('-h, --help')->description('See help and usage');
	}

	public function __invoke($args)
	{
		return $this->parse($args);
	}

	public function bool($arg)
	{
		return $this->add($arg, 'Boolean');
	}

	public function int($arg)
	{
		return $this->add($arg, 'Integer');
	}

	public function str($arg)
	{
		return $this->add($arg, 'String');
	}

	public function parse($args = null)
	{
		if (is_null($args)) {
			global $argv;
			$args = $argv;
		}
		if (!is_array($args)) {
			throw new Exception\SyntaxError('parse takes an array');
		}
		try {
			$result = $this->parse_args($args);
		} catch (Exception\ArgsError\ShowUsage $e) {
			$this->usage();
			throw new Exception\Abort(2);
		} catch (Exception\UserError $e) {
			stderr('error: '.$e->getMessage()."\n\n");
			$this->usage();
			throw new Exception\Abort(2);
		}
		assert('is_object($result)');
		return $result;
	}

	public function usage()
	{
		$str = 'usage: '
			.($this->program ? $this->program . ' ' : '')
			.implode(' ', array_map(function ($p) {
				$brackets = $p->required ? '<>' : '[]';
				return $brackets[0].$p->name.$brackets[1];
			}, $this->positional))
			."\n\n";

		$str .= $this->description."\n\n";

		if ($this->positional) {
			$str .= 'positional arguments:'."\n";
			foreach ($this->positional as $p) {
				$str .= '  '.$p->_usage()."\n";
			}
			$str .= "\n";
		}

		if ($this->options) {
			$str .= 'optional arguments: '."\n";
			$opts = $this->options;
			usort($opts, function ($a, $b) {
				return strnatcmp($a->name, $b->name);
			});
			foreach ($opts as $option) {
				$str .= '  ' . $option->_usage();
			}
		}
		stderr($str);
	}



	private $positional = array();
	private $program;
	private $options = array();

	private function add($arg, $className)
	{
		if (substr($arg, 0, 1) === '-') {
			$className = '\\Slik\\Uncia\\' . $className . 'Option';
			$o = new $className($arg);
			$this->options[] = $o;
			return $o;
		}

		$className = '\\Slik\\Uncia\\' . $className . 'Positional';
		$p = new $className($arg);
		$this->positional[] = $p;
		return $p;
	}

	private function normalize_args($args)
	{
		$result = array();

		foreach ($args as $a) {
			if (preg_match('/^-([a-z0-9]{2,})$/', $a, $m)) {
				foreach (str_split($m[1]) as $char) {
					$result[] = '-'.$char;
				}
				continue;
			}
			if (preg_match('/^(--[a-z0-9]+)=(.+)$/', $a, $m)) {
				$result[] = $m[1];
				$result[] = $m[2];
				continue;
			}
			$result[] = $a;
		}
		return $result;
	}

	private function parse_args($args)
	{
		$result = new \stdClass();

		foreach ($this->options as $option) {
			$default = $option->default;
			$result->{$option->name} = is_callable($default) ? $default() : $default;
		}
		foreach ($this->positional as $positional) {
			$default = $positional->default;
			$result->{$positional->name} = is_callable($default) ? $default() : $default;
		}

		$args = $this->normalize_args($args);

		$positional_stack = $this->positional;

		$options_enabled = true;

		$this->program = reset($args);

		// If program has required arguments and we haven't passed any,
		// show usage without throwing an error
		if (isset($this->positional[count($args) - 1])
			&& $this->positional[count($args) - 1]->required
		) {
			throw new Exception\ArgsError\ShowUsage();
		}

		while ($value = next($args)) {
			if ($value === '--') {
				$options_enabled = false;
				continue;
			}

			$argument = null;
			if (substr($value, 0, 1) === '-' && $options_enabled) {
				if (in_array($value, array('-h', '--help'))) {
					throw new Exception\ArgsError\ShowUsage();
				}

				// Find which option is this
				try {
					if (substr($value, 0, 2) === '--') {
						$argument = self::find($this->options, 'name', substr($value, 2));
					} else {
						$argument = self::find($this->options, 'short', substr($value, 1));
					}
				} catch (Exception\NotFound $e) {
					throw new Exception\UserError('Unknown option '.$value);
				}
			} else {
				if (!$positional_stack) {
					throw new Exception\UserError('Too many positional arguments');
				}
				$argument = array_shift($positional_stack);
			}

			try {
				if ($argument->_needsValue) {
					$value = next($args);
					if ($value === false || substr($value, 0, 1) === '-') {
						throw new Exception\ValueError('Missing value');
					}
					$value = $argument->value($value);
				} else {
					$value = $argument->value($value);
				}
				$result->{$argument->name} = $value;
			} catch (Exception\ValueError $e) {
				throw new Exception\UserError($e->getMessage().' for '.$argument->name);
			}

			$result->{$argument->name} = $value;
		}

		foreach ($positional_stack as $positional) {
			if ($positional->required) {
				throw new Exception\UserError('Value for '.$positional->name.' is missing.');
			}
		}

		return $result;
	}

	private static function find($array, $key, $value)
	{
		foreach ($array as $obj) {
			if (property_exists($obj, $key) && $obj->$key === $value) {
				return $obj;
			}
		}
		throw new Exception\NotFound();
	}
}


abstract class Argument
{
	public $default, $description, $name;
	public $_needsValue = false, $_valueListAllowed = true, $_valueList = array();

	abstract public function __construct($string);

	public function __call($f, $args)
	{
		if (in_array($f, array('default', 'list'))) {
			return call_user_func_array(array($this, '_'.$f), $args);
		}
		throw new SyntaxError('Unknown method '.$f);
	}

	/**
	 * You can call as ->default
	 */
	public function _default($default)
	{
		$this->default = $default;
		return $this;
	}

	public function description($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * You can call as ->list
	 */
	public function _list($list)
	{
		if (!$this->_valueListAllowed) {
			throw new SyntaxError('Can not set a list of values to a '.get_class($this));
		}
		if (is_string($list)) {
			if (strpos($list, ',') === false) {
				throw new SyntaxError('List of values should be comma-separated');
			}
			$list = array_map('trim', explode(',', $list));
		}
		if (!is_array($list)) {
			throw new SyntaxError('List of values should be array or comma-separated string');
		}
		$this->_valueList = $list;
		return $this;
	}

	public function value($value)
	{
		$value = $this->_value($value);
		if (is_array($this->_valueList)) {
			assert('$this->_valueListAllowed');
			if ($this->_valueList && !in_array($value, $this->_valueList)) {
				throw new Exception\UserError($this->name
					.' can only be one of the following: '
					.implode(', ', $this->_valueList)
					.'.');
			}
		}
		return $value;
	}

	public function _usage()
	{
		$values = array_map(function ($v) {
			if ($v === true) return 'true';
			if ($v === false) return 'false';
			return $v;
		}, $this->_valueList);
		$extras = array($this->description, implode(', ', $values));
		$extras = array_filter($extras);
		return $this->name . ($extras ?  ': '.implode('; ', $extras) : '');
	}

	public function _value($value)
	{
		return $value;
	}
}

abstract class Positional extends Argument
{
	public $required = false;

	public function __construct($string)
	{
		$this->name = $string;
	}

	public function _default($default)
	{
		if ($this->required) {
			throw new SyntaxError('Can not set default value to a required argument');
		}
		return parent::_default($default);
	}

	public function required()
	{
		if ($this->default) {
			throw new SyntaxError('Can not make an argument required if it has a default value');
		}
		$this->required = true;
		return $this;
	}
}

class BooleanPositional extends Positional
{
	public $_valueListAllowed = false;

	public function _value($value)
	{
		return in_array(strtolower($value), array('1', 'true', 'yes', 'y'));
	}
}

class IntegerPositional extends Positional
{
	public function _value($value)
	{
		if (!ctype_digit($value)) {
			throw new Exception\ValueError($value.' is not a number');
		}
		return (int) $value;
	}
}

class StringPositional extends Positional
{
}

abstract class Option extends Argument
{
	public $meta, $short;
	public $_needsValue = true, $_valueListAllowed = true;

	public function __construct($string)
	{
		$this->_valueListAllowed = $this->_needsValue;
		if (preg_match('/(?:^| |,)--([a-z0-9_-]+)\b(?:=([a-z0-9_-]+))?/i', $string, $m)) {
			$this->name = $m[1];
			if (isset($m[2])) {
				if (preg_match('/^[A-Z]+$/', $m[2])) {
					$this->meta = $m[2];
				} else {
					$this->default = $m[2];
				}
			}
			if (preg_match_all('/(?:^| |,)-([a-z0-9])\b/i', $string, $m)) {
				if (count($m[1]) > 1) {
					throw new SyntaxError('More than one short option given');
				}
				$this->short = $m[1][0];
			}
		} else {
			throw new SyntaxError('long option name is missing');
		}
	}

	public function meta($meta)
	{
		$this->meta = $meta;
		return $this;
	}

	public function _usage()
	{
		$str = '';
		$str .= $this->short ? '-'.$this->short . ',' : '  ';
		$str .= ' --'.$this->name;
		if ($this->meta) {
			$str .= '='.$this->meta;
		}
		if ($this->description) {
			$str .= ': '.$this->description;
		}
		if ($this->default && !is_callable($this->default)) {
			$str .= ' (default: '.$this->default.')';
		}
		return $str . "\n";
	}
}

class BooleanOption extends Option
{
	public $default = false, $_needsValue = false;

	public function _value($value)
	{
		return !$this->default;
	}
}

class IntegerOption extends Option
{
	public $meta = 'N';

	public function _value($value)
	{
		if (!ctype_digit($value)) {
			throw new Exception\ValueError($value.' is not a number');
		}
		return (int) $value;
	}
}

class StringOption extends Option
{
	public $meta = 'STR';
}
