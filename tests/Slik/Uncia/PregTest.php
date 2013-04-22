<?php
namespace Slik\Uncia;

class PregTest extends \PHPUnit_Framework_TestCase
{
	public function dataNotFound()
	{
		return array(
			array('/([a-z]+)/', ' 23 23 223'),
		);
	}

	public function dataSimple()
	{
		return array(
			array('john', '/[a-z]+/', ' 23 23 john223'),
			array('john', '/([a-z]+)/', ' 23 23 john223'),
			array(array('john', 'snow'), '/([a-z]+) ([a-z]+)/', ' 23 23 john snow223'),
		);
	}

	/**
	 * @dataProvider dataNotFound
	 */
	public function testNotFound($pattern, $subject)
	{
		$this->setExpectedException('\\Slik\\Uncia\\Exception\\NotFound');
		preg($pattern, $subject);
	}

	/**
	 * @dataProvider dataSimple
	 */
	public function testSimple($target, $pattern, $subject)
	{
		$this->assertSame($target, preg($pattern, $subject));
	}
}
