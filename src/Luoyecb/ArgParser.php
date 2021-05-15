<?php
namespace Luoyecb;

use \Exception;
use \ArrayAccess;

/**
 * A simple command line option parser.
 */
class ArgParser implements ArrayAccess {
	// Supported option types
	const TYPE_INT = 'INT';
	const TYPE_FLOAT = 'FLOAT';
	const TYPE_BOOL = 'BOOL';
	const TYPE_STRING = 'STRING';

	private static $typeCheckers = [
		self::TYPE_INT => 'is_int',
		self::TYPE_FLOAT => 'is_float',
		self::TYPE_BOOL => 'is_bool',
		self::TYPE_STRING => 'is_string',
	];

	private $opts = [];
	private $parsedOpts = [];
	private $args = [];
	private $isParsed = false;

	public function addBool(string $name, $default, string $help = ''): ArgParser {
		$this->addOption($name, self::TYPE_BOOL, $default, $help);
		return $this;
	}

	public function addInt(string $name, $default, string $help = ''): ArgParser {
		$this->addOption($name, self::TYPE_INT, $default, $help);
		return $this;
	}

	public function addFloat(string $name, $default, string $help = ''): ArgParser {
		$this->addOption($name, self::TYPE_FLOAT, $default, $help);
		return $this;
	}

	public function addString(string $name, $default, string $help = ''): ArgParser {
		$this->addOption($name, self::TYPE_STRING, $default, $help);
		return $this;
	}

	private function addOption(string $name, string $type, $default, string $help = '') {
		$throwException = false;
		switch ($type) {
			case self::TYPE_INT:
			case self::TYPE_FLOAT:
			case self::TYPE_BOOL:
			case self::TYPE_STRING:
				$throwException = !self::$typeCheckers[$type]($default);
				break;
			default:
				throw new InvalidArgumentException(sprintf('Unknown option type[%s].', $type));
		}
		if ($throwException) {
			throw new InvalidArgumentException(sprintf("Invalid option value, must be %s.", $type));
		}

		$this->opts[$name] = [
			't' => $type,
			'v' => $default,
			'h' => $help,
		];
	}

	public function getArgs(): array {
		return $this->args;
	}

	public function getOptions(): array {
		return $this->parsedOpts;
	}

	public function getOption(string $name) {
		return $this->parsedOpts[$name] ?? NULL;
	}

	public function buildUsage(): string {
		$helpArrs = [];

		$maxLen = 0;
		foreach ($this->opts as $k=>$v) {
			$k .= ($v['t']==self::TYPE_BOOL ? ":" : sprintf("=%s:", $v['t']));
			$helpArrs[$k] = ucfirst($v['h']);
			$maxLen = max($maxLen, strlen($k));
		}
		ksort($helpArrs, SORT_STRING);

		global $argv;
		$binName = basename($argv[0], '.php');

		$infoStr = "${binName} [Option] [Args...]" . PHP_EOL . PHP_EOL;
		$infoStr .= "Option:" . PHP_EOL;
		foreach ($helpArrs as $k=>$v) {
			$infoStr .= sprintf("  -%s %s".PHP_EOL, str_pad($k, $maxLen, " "), $v);
		}
		return $infoStr;
	}

	public function parse() {
		if ($this->isParsed) {
			return;
		}

		// First, set default value.
		foreach ($this->opts as $k => $v) {
			$this->parsedOpts[$k] = $v['v'];
		}

		global $argv;
		$idx = 1;
		$len = count($argv);
		while ($idx < $len) {
			$currOpt = $argv[$idx++];
			if ($currOpt == '--') {
				$this->args = array_slice($argv, $idx);
				break;
			}

			$name = $this->isValidOption($currOpt);
			if ($name === false) {
				$this->args = array_slice($argv, $idx-1);
				break;
			}

			// Handle -key=value syntax
			$pos = StringUtil::containsFirstPos($name, '=');
			if ($pos !== false) {
				$val = substr($name, $pos+1);
				$name = substr($name, 0, $pos);
				if (!empty($name) && $this->optionExists($name)) {
					$this->parseOption($this->optionType($name), $name, $val);
				}
				continue;
			}

			// Unknown option, ignored
			if (!$this->optionExists($name)) {
				continue;
			}

			$type = $this->optionType($name);
			switch ($type) {
				case self::TYPE_BOOL:
					$val = true;
					break;
				case self::TYPE_INT:
				case self::TYPE_FLOAT:
				case self::TYPE_STRING:
					if ($idx >= $len) {
						throw new Exception(sprintf("Option[%s] require value.", $name));
					}
					$val = $argv[$idx];
					break;
			}
			if ($this->parseOption($type, $name, $val)) {
				$idx++;
			}
		}

		$this->isParsed = true;
	}

	private function optionExists($name): bool {
		return isset($this->opts[$name]);
	}

	private function optionType($name): string {
		return $this->opts[$name]['t'];
	}

	private function isValidOption(string $opt) {
		$len = strlen($opt);
		if (empty($opt) || $len <= 1) {
			return false;
		}

		if (StringUtil::equalsLen($opt, '--', 2)) {
			if ($len == 2) {
				return false;
			} else {
				return substr($opt, 2); // Without '--'
			}
		}

		if ($opt[0] == '-') {
			return substr($opt, 1); // Without '-'
		}
		return false;
	}

	private function parseOption($type, $name, $val): bool {
		$throwException = false;
		switch ($type) {
			case self::TYPE_BOOL:
				$this->parsedOpts[$name] = true; // Ignore $val
				return false;
			case self::TYPE_INT:
			case self::TYPE_FLOAT:
				$val = $this->parseNumeric($val, $type);
				if ($val !== false) {
					$this->parsedOpts[$name] = $val;
					return true;
				}
				$throwException = true;
				break;
			case self::TYPE_STRING:
				$check = $this->isValidOption($val);
				if ($check === false) {
					$this->parsedOpts[$name] = $val;
					return true;
				}
				$throwException = true;
				break;
		}
		if ($throwException) {
			throw new Exception(sprintf("Option[%s] require %s value.", $name, $type));
		}
		return true;
	}

	private function parseNumeric(string $str, string $type) {
		if (is_numeric($str)) {
			$isFloat = StringUtil::containsAny($str, '.', 'e', 'E');
			if ($type == self::TYPE_INT && !$isFloat) {
				return intval($str);
			}
			if ($type == self::TYPE_FLOAT) {
				return floatval($str);
			}
		}
		return false;
	}

	public function offsetExists($key): bool {
		return $this->optionExists($key);
	}

	public function offsetGet($key) {
		return $this->getOption($key);
	}

	public function offsetSet($key, $val) {
		throw new Exception('Operation not supported.');
	}

	public function offsetUnset($key) {
		throw new Exception('Operation not supported.');
	}
}
