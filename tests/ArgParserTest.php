<?php
use PHPUnit\Framework\TestCase;
use Luoyecb\ArgParser;

class ArgParserTest extends TestCase {

	public function dataProvider() {
	return [
		[
			['', '-flag', '--times', '3', '-salary', '14000', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
			[true, 3, floatval(14000), 'this is test msg'],
			['arg1', 'arg2', 'arg3'],
		],
		[
			// -key=value
			['', '-flag=true', '--times=3', '-salary=14000.00', '-msg=this is test msg', '-times', '4', 'arg1', 'arg2', 'arg3'],
			[true, 4, 14000.00, 'this is test msg'],
			['arg1', 'arg2', 'arg3'],
		],
		[
			['', '-flag', '--times =3', '-salary= 14000.00', '-msg= this is test msg', 'arg1', 'arg2', 'arg3'],
			[true, 0, 14000.00, ' this is test msg'],
			['arg1', 'arg2', 'arg3'],
		],
		[
			// --
			['', '-flag', '-times', '3', '-salary', '14000.00', '--', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
			[true, 3, 14000.00, ''],
			['-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
		],
		[
			// unknown option '-salaryyyy'
			['', '-flag', '-times', '3', '-salaryyyy', '14000.00', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
			[true, 3, 0.0, ''],
			['14000.00', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
		],
		[
			// invalid option '==='
			['', '-flag', '-times', '3', '===', '-salaryyyy', '14000.00', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
			[true, 3, 0.0, ''],
			['===', '-salaryyyy', '14000.00', '-msg', 'this is test msg', 'arg1', 'arg2', 'arg3'],
		],
	];
	}

	public function buildArgParser(array $input): ArgParser {
		global $argv;
		$argv = $input;

		$parser = new ArgParser();
		$parser->addBool('flag', false, "test flag")
			->addInt('times', 0, "output times")
			->addFloat('salary', 0.0, "job salary, float")
			->addString('msg', '', "description")
			->addBool('help', false, "show this help information")
			->parse();

		return $parser;
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testNormal($input, $expected, $expectedArgs) {
		$parser = $this->buildArgParser($input);
		extract($parser->getOptions());

		$this->assertSame($expected[0], $flag);
		$this->assertSame($expected[1], $times);
		$this->assertSame($expected[2], $salary);
		$this->assertSame($expected[3], $msg);

		$this->assertSame($expected[0], $parser->getOption('flag'));
		$this->assertSame($expected[1], $parser->getOption('times'));
		$this->assertSame($expected[2], $parser->getOption('salary'));
		$this->assertSame($expected[3], $parser->getOption('msg'));

		$this->assertSame($expected[0], $parser['flag']);
		$this->assertSame($expected[1], $parser['times']);
		$this->assertSame($expected[2], $parser['salary']);
		$this->assertSame($expected[3], $parser['msg']);

		$this->assertSame($expectedArgs, $parser->getArgs());

		$this->assertNull($parser->getOption('unexists_flag'));
		$this->assertNull($parser['unexists_flag']);
	}

	/**
	 * @expectedException Exception
	 */
	public function testArrayAccessException() {
		$parser = $this->buildArgParser(['']);
		$parser['flag'] = true;
	}

	/**
	 * @expectedException Exception
	 */
	public function testArrayAccessException2() {
		$parser = $this->buildArgParser(['']);
		unset($parser['times']);
	}

	/**
	 * @expectedException Exception
	 */
	public function testException() {
		$input = ['', '-flag', '-times', '3', '-salary', '14000.00', '-msg'];
		$parser = $this->buildArgParser($input);
	}

	/**
	 * @expectedException Exception
	 */
	public function testException2() {
		$input = ['', '-flag', '-times', '3', '-salary', '14000.00', '-msg', '-flag'];
		$parser = $this->buildArgParser($input);
	}

	/**
	 * @expectedException Exception
	 */
	public function testException3() {
		$input = ['', '-flag', '-times', '3', '-salary', '14000.00===', '-msg', 'this is test msg.'];
		$parser = $this->buildArgParser($input);
	}

	public function testUsage() {
		$parser = $this->buildArgParser(['ArgParser']);
		$info = $parser->buildUsage();
		// echo PHP_EOL;
		// var_dump($info);
		$this->assertTrue(strlen($info) > 0);
	}

}
