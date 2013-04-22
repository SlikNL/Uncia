<?php
namespace Slik\Uncia;

class ArgsTest extends \PHPUnit_Framework_TestCase
{
	public function dataValues()
	{
		return array(
			array('str', 'name', 'john', 'john'),
			array('bool', 'love', true, 'yes'),
			array('bool', 'love', true, '1'),
			array('bool', 'love', false, 'no'),
			array('bool', 'love', false, '0'),
			array('int', 'clouds', 32, '32'),
			array('int', 'clouds', 0, '0'),
			array('int', 'clouds', -3, '-- -3'),
		);
	}

	/**
	 * @dataProvider dataValues
	 */
	public function testPositional($type, $name, $value, $args)
	{
		$this->args->$type($name);
		$this->check($value, $name, $args);
	}

	/**
	 * @dataProvider dataValues
	 */
	public function testPositionalDefault($type, $name)
	{
		$this->args->$type($name);
		$this->check(null, $name, '');
	}

	/**
	 * @dataProvider dataValues
	 */
	public function testPositionalDefaultCallback($type, $name, $value)
	{
		$this->args->$type($name)->default(function () use ($value) {
			return $value;
		});
		$this->check($value, $name, '');
	}

	/**
	 * @dataProvider dataValues
	 */
	public function testPositionalDefaultCallbackNotCalled($type, $name, $value, $args)
	{
		$test = $this;
		$this->args->$type($name)->default(function () use ($test) {
			$test->fail('Default callback should not be called when value given');
		});
		$this->check($value, $name, $args);
	}

	/**
	 * @dataProvider dataValues
	 */
	public function testPositionalDefaultSet($type, $name, $value, $args)
	{
		$this->args->$type($name)->default($value);
		$this->check($value, $name, '');
	}

	public function testPositionalList()
	{
		$this->args->str('name')->list('john,mark');
		$this->check('mark', 'name', 'mark');
	}

	public function testPositionalListIncorrect()
	{
		$this->args->str('name')->list('john,mark');
		$this->setExpectedException('\\Slik\\Uncia\\Exception\\UserError');
		$this->parse('rock');
	}

	public function testPositionalListSyntaxError()
	{
		$this->setExpectedException('\\Slik\\Uncia\\Exception\\SyntaxError');
		$this->args->str('name')->list('john');
	}

	public function testPositionalMultiple()
	{
		$this->args->int('clouds');
		$this->args->bool('love');
		$this->check(314, 'clouds', '314 y');
		$this->check(true, 'love', '314 y');
	}

	/**
	 * @dataProvider dataValues
	 */
	public function testPositionalRequired($type, $name)
	{
		$this->args->$type($name)->required();
		$this->setExpectedException('\\Slik\\Uncia\\Exception\\UserError');
		$this->parse('');
	}

	public function testPositionalRequiredSecond()
	{
		$this->args->int('clouds')->required();
		$this->args->bool('love')->required();
		$this->setExpectedException('\\Slik\\Uncia\\Exception\\UserError');
		$this->parse('42');
	}

	protected $args;

	protected function setUp()
	{
		$this->args = new Args('Sample description');
	}

	private function check($target, $property, $args)
	{
		$this->assertSame($target, $this->parse($args)->$property);
	}

	private function parse($args)
	{
		if (!is_array($args)) {
			$args = explode(' ', $args);
		}
		$args = array_filter($args, function ($value) {
			return $value !== '';
		});
		array_unshift($args, 'command');
		return $this->args->parse($args, true);
	}
}
