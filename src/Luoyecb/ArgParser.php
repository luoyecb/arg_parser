<?php
namespace Luoyecb;

/**
 * A simple command line option parser.
 * @author guolinchao
 * @email  luoyecb@163.com
 */
class ArgParser
{
	/**
	 * option type
	 */
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_BOOL = 'bool';
	const TYPE_STRING = 'str';

	/**
	 * all options
	 * 
	 * @var array
	 */
	protected static $opts = [];

	/**
	 * all args
	 * 
	 * @var array
	 */
	protected static $args = [];

	protected static $isParsed = false;

	/**
	 * add an option
	 * 
	 * @param string $name
	 * @param string $type
	 * @param mixed $default default value
	 */
	public static function addArgument($name, $type, $default) {
		// check option type
		switch ($type) {
			case self::TYPE_INT:
			case self::TYPE_FLOAT:
			case self::TYPE_BOOL:
			case self::TYPE_STRING:
				break;
			default:
				throw new InvalidArgumentException(sprintf('unknown option type[%s].', $type));
		}

		self::$opts[$name] = [
			't' => $type,
			'v' => $default,
		];
	}

	/**
	 * add bool option
	 */
	public static function addBool($name, $default) {
		self::addArgument($name, self::TYPE_BOOL, $default);
	}

	/**
	 * add int option
	 */
	public static function addInt($name, $default) {
		self::addArgument($name, self::TYPE_INT, $default);
	}

	/**
	 * add float option
	 */
	public static function addFloat($name, $default) {
		self::addArgument($name, self::TYPE_FLOAT, $default);
	}

	/**
	 * add string option
	 */
	public static function addString($name, $default) {
		self::addArgument($name, self::TYPE_STRING, $default);
	}

	/**
	 * check is a valid option flag
	 * 
	 * @param  string $opt
	 * @return boolean|string
	 */
	protected static function isOpt($opt) {
		$opt = trim($opt);
		if (empty($opt) || strlen($opt) <= 1) {
			return false;
		}
		if ($opt[0] == '-') {
			return substr($opt, 1); // option name without '-'
		}
		return false;
	}

	/**
	 * check is a valid number
	 * 
	 * @param  string $str
	 * @return boolean|float|integer
	 */
	protected static function getNumeric($str) {
		if (is_numeric($str)) {
			return $str + 0; // change to int or float
		}
		return false; // not number
	}

	public static function getArgs() {
		return self::$args;
	}

	public static function getOptions() {
		return self::$opts;
	}

	public static function getOption($name) {
		if (self::$isParsed) {
			return self::$opts[$name]; // actual value
		}
		return self::$opts[$name]['v']; // default value
	}

	/**
	 * parse options
	 * 
	 * @return array
	 */
	public static function parse() {
		global $argv;
		if (!self::$isParsed) {
			$idx = 1; // index start from 1.
			$len = count($argv);
			while ($idx < $len) {
				$cur = $argv[$idx];
				// parse args
				if (!($name = self::isOpt($cur))) {
					self::$args = array_slice($argv, $idx);
					break;
				} else {
					// parse options
					if (!isset(self::$opts[$name])) {
						$idx++;
						continue;
					}
					// default value
					$dft = self::$opts[$name];
					$idx++; // handle next argument
					switch ($dft['t']) {
					case self::TYPE_BOOL:
						self::$opts[$name]['v'] = true;
						break;
					case self::TYPE_INT:
						if ( ($int = self::getNumeric($argv[$idx])) !== false
							&& is_int($int) ) {
							self::$opts[$name]['v'] = $int;
							$idx++;
						}
						break;
					case self::TYPE_FLOAT:
						if ( ($float = self::getNumeric($argv[$idx])) !== false
							&& is_float($float) ) {
							self::$opts[$name]['v'] = $float;
							$idx++;
						}
						break;
					case self::TYPE_STRING:
						if (!self::isOpt($argv[$idx])) {
							self::$opts[$name]['v'] = $argv[$idx];
							$idx++;
						}
					}
				}
			}

			foreach (self::$opts as $name=>$v) {
				self::$opts[$name] = $v['v'];
			}

			self::$isParsed = true;
		}

		return self::$opts;
	}
}
