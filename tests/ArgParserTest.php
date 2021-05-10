<?php
use PHPUnit\Framework\TestCase;
use Luoyecb\ArgParser;

class ArgParserTest extends TestCase {

	public static function setUpBeforeClass() {
		ArgParser::setDebug(true);
	}

	public function testNormal() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'--times',
			'3',
			'-salary',
			'14000.00',
			'-msg',
			'this is test msg',
			'arg1',
			'arg2',
			'arg3',
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
		extract(ArgParser::getOptions());
		$args = ArgParser::getArgs();

		$this->assertTrue($flag);
		$this->assertSame($times, 3);
		$this->assertSame($salary, 14000.00);
		$this->assertSame($msg, 'this is test msg');
		$this->assertSame($args, ['arg1', 'arg2', 'arg3']);

		$this->assertSame($flag, ArgParser::getOption('flag'));
		$this->assertSame($times, ArgParser::getOption('times'));
		$this->assertSame($salary, ArgParser::getOption('salary'));
		$this->assertSame($msg, ArgParser::getOption('msg'));
	}

	// case: '--'
	public function testLine() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'-times',
			'3',
			'-salary',
			'14000.00',
			'--',
			'-msg',
			'this is test msg',
			'arg1',
			'arg2',
			'arg3',
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
		extract(ArgParser::getOptions());
		$args = ArgParser::getArgs();

		$this->assertTrue($flag);
		$this->assertSame($times, 3);
		$this->assertSame($salary, 14000.00);
		$this->assertSame($msg, '');
		$this->assertSame($args, ['-msg', 'this is test msg', 'arg1', 'arg2', 'arg3']);
	}

	// case: unknown option '-salaryyyy'
	public function testUnknownOption() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'-times',
			'3',
			'-salaryyyy',
			'14000.00',
			'-msg',
			'this is test msg',
			'arg1',
			'arg2',
			'arg3',
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
		extract(ArgParser::getOptions());
		$args = ArgParser::getArgs();

		$this->assertTrue($flag);
		$this->assertSame($times, 3);
		$this->assertSame($salary, 0.0);
		$this->assertSame($msg, '');
		$this->assertSame($args, ['14000.00', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3']);
	}

	// case: invalid option '==='
	public function testInvalidOption() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'-times',
			'3',
			'===',
			'-salaryyyy',
			'14000.00',
			'-msg',
			'this is test msg',
			'arg1',
			'arg2',
			'arg3',
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
		extract(ArgParser::getOptions());
		$args = ArgParser::getArgs();

		$this->assertTrue($flag);
		$this->assertSame($times, 3);
		$this->assertSame($salary, 0.0);
		$this->assertSame($msg, '');
		$this->assertSame($args, ['===', '-salaryyyy', '14000.00', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3']);
	}

	/**
	 * @expectedException Exception
	 */
	public function testException() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'-times',
			'3',
			'-salary',
			'14000.00',
			'-msg',
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
	}

	/**
	 * @expectedException Exception
	 */
	public function testException2() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'-times',
			'3',
			'-salary',
			'14000.00',
			'-msg',
			'-flag'
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
	}

	/**
	 * @expectedException Exception
	 */
	public function testException3() {
		global $argv;
		$argv = [
			'ArgParserTest.php',
			'-flag',
			'-times',
			'3',
			'-salary',
			'14000.00===',
			'-msg',
			'this is test msg.'
		];

		ArgParser::addBool('flag', false);
		ArgParser::addInt('times', 0);
		ArgParser::addFloat('salary', 0.0);
		ArgParser::addString('msg', '');
		ArgParser::parse();
	}

}
