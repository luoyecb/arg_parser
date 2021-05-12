<?php
use PHPUnit\Framework\TestCase;
use Luoyecb\ArgParser;

class ArgParserTest extends TestCase {

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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
		extract($parser->getOptions());
		$args = $parser->getArgs();

		$this->assertTrue($flag);
		$this->assertSame($times, 3);
		$this->assertSame($salary, 14000.00);
		$this->assertSame($msg, 'this is test msg');
		$this->assertSame($args, ['arg1', 'arg2', 'arg3']);

		$this->assertSame($flag, $parser->getOption('flag'));
		$this->assertSame($times, $parser->getOption('times'));
		$this->assertSame($salary, $parser->getOption('salary'));
		$this->assertSame($msg, $parser->getOption('msg'));
		$this->assertNull($parser->getOption('unexists_flag'));
	}

	public function testArrayAccess() {
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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
		extract($parser->getOptions());
		$args = $parser->getArgs();

		$this->assertTrue($flag);
		$this->assertSame($times, 3);
		$this->assertSame($salary, 14000.00);
		$this->assertSame($msg, 'this is test msg');
		$this->assertSame($args, ['arg1', 'arg2', 'arg3']);

		$this->assertSame($flag, $parser['flag']);
		$this->assertSame($times, $parser['times']);
		$this->assertSame($salary, $parser['salary']);
		$this->assertSame($msg, $parser['msg']);
		$this->assertNull($parser['unexists_flag']);
	}

	/**
	 * @expectedException Exception
	 */
	public function testArrayAccessException() {
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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();

		$parser['flag'] = true;
	}

	/**
	 * @expectedException Exception
	 */
	public function testArrayAccessException2() {
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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();

		unset($parser['times']);
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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
		extract($parser->getOptions());
		$args = $parser->getArgs();

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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
		extract($parser->getOptions());
		$args = $parser->getArgs();

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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
		extract($parser->getOptions());
		$args = $parser->getArgs();

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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
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

		$parser = new ArgParser();
		$parser->addBool('flag', false);
		$parser->addInt('times', 0);
		$parser->addFloat('salary', 0.0);
		$parser->addString('msg', '');
		$parser->parse();
	}

}
