<?php
namespace Luoyecb;

use \Exception;

// A simple command line option parser.
class ArgParser
{
	// Supported option types
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_BOOL = 'bool';
	const TYPE_STRING = 'str';

	private static $opts = [];
	private static $parsedOpts = [];
	private static $args = [];

	private static $isParsed = false;
	private static $isDebug = false;

	public static function setDebug(bool $debug) {
		self::$isDebug = $debug;
	}

	public static function addBool(string $name, $default) {
		self::addOption($name, self::TYPE_BOOL, $default);
	}

	public static function addInt(string $name, $default) {
		self::addOption($name, self::TYPE_INT, $default);
	}

	public static function addFloat(string $name, $default) {
		self::addOption($name, self::TYPE_FLOAT, $default);
	}

	public static function addString(string $name, $default) {
		self::addOption($name, self::TYPE_STRING, $default);
	}

	public static function addOption(string $name, string $type, $default) {
		switch ($type) {
		case self::TYPE_INT:
			if (!is_int($default)) {
				throw new InvalidArgumentException(sprintf("invalid option value, must be int."));
			}
			break;
		case self::TYPE_FLOAT:
			if (!is_float($default)) {
				throw new InvalidArgumentException(sprintf("invalid option value, must be float."));
			}
			break;
		case self::TYPE_BOOL:
			if (!is_bool($default)) {
				throw new InvalidArgumentException(sprintf("invalid option value, must be bool."));
			}
			break;
		case self::TYPE_STRING:
			if (!is_string($default)) {
				throw new InvalidArgumentException(sprintf("invalid option value, must be string."));
			}
			break;
		default:
			throw new InvalidArgumentException(sprintf('unknown option type[%s].', $type));
		}

		self::$opts[$name] = [
			't' => $type,
			'v' => $default,
		];
	}

	public static function getArgs(): array {
		return self::$args;
	}

	public static function getOptions(): array {
		return self::$parsedOpts;
	}

	public static function getOption(string $name) {
		if (isset(self::$parsedOpts[$name])) {
			return self::$parsedOpts[$name];
		}
		throw new Exception(sprintf('unknown option[%s].', $name));
	}

	public static function parse() {
		if (!self::$isDebug && self::$isParsed) {
			return;
		}

		global $argv;
		$idx = 1;
		$len = count($argv);

		while ($idx < $len) {
			if ($argv[$idx] == '--') {
				self::$args = array_slice($argv, $idx+1);
				break;
			}

			$name = self::isValidOption($argv[$idx]);
			if ($name === false) {
				self::$args = array_slice($argv, $idx);
				break;
			} else {
				if (!isset(self::$opts[$name])) {
					// unknown option, ignored
					$idx++;
					continue;
				}

				$default = self::$opts[$name];
				$idx++;
				switch ($default['t']) {
				case self::TYPE_BOOL:
					self::$opts[$name]['v'] = true;
					break;
				case self::TYPE_INT:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
					}

					$val = self::isNumeric($argv[$idx]);
					if ($val !== false && is_int($val)) {
						self::$opts[$name]['v'] = $val;
						$idx++;
					} else {
						throw new Exception(sprintf("option[%s] require int value.", $name));
					}
					break;
				case self::TYPE_FLOAT:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
					}

					$val = self::isNumeric($argv[$idx]);
					if ($val !== false && is_float($val) ) {
						self::$opts[$name]['v'] = $val;
						$idx++;
					} else {
						throw new Exception(sprintf("option[%s] require float value.", $name));
					}
					break;
				case self::TYPE_STRING:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
					}

					$val = self::isValidOption($argv[$idx]);
					if ($val === false) {
						self::$opts[$name]['v'] = $argv[$idx];
						$idx++;
					} else {
						throw new Exception(sprintf("option[%s] require string value.", $name));
					}
				}
			}
		}

		foreach (self::$opts as $name => $val) {
			self::$parsedOpts[$name] = $val['v'];
		}

		self::$isParsed = true;
	}

	private static function isValidOption(string $opt) {
		$opt = trim($opt);
		$len = strlen($opt);
		if (empty($opt) || $len <= 1) {
			return false;
		}

		if (self::strEqual($opt, '--', 2)) {
			if ($len == 2) {
				return false;
			} else {
				return substr($opt, 2); // without '--'
			}
		}
		if ($opt[0] == '-') {
			return substr($opt, 1); // without '-'
		}

		return false;
	}

	private static function strEqual(string $str1, string $str2, int $length): bool {
		return strncmp($str1, $str2, $length) === 0;
	}

	private static function isNumeric(string $str) {
		if (is_numeric($str)) {
			return $str + 0;
		}
		return false;
	}
}
