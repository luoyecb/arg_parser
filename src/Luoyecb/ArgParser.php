<?php
namespace Luoyecb;

use \Exception;
use \ArrayAccess;

// A simple command line option parser.
class ArgParser implements ArrayAccess {
	// Supported option types
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_BOOL = 'bool';
	const TYPE_STRING = 'str';

	private $opts = [];
	private $parsedOpts = [];
	private $args = [];

	private $isParsed = false;

	public function addBool(string $name, $default) {
		$this->addOption($name, self::TYPE_BOOL, $default);
	}

	public function addInt(string $name, $default) {
		$this->addOption($name, self::TYPE_INT, $default);
	}

	public function addFloat(string $name, $default) {
		$this->addOption($name, self::TYPE_FLOAT, $default);
	}

	public function addString(string $name, $default) {
		$this->addOption($name, self::TYPE_STRING, $default);
	}

	public function addOption(string $name, string $type, $default) {
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

		$this->opts[$name] = [
			't' => $type,
			'v' => $default,
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

	public function parse() {
		if ($this->isParsed) {
			return;
		}

		// first, set default value
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

			// case: -key=value
			if (($pos = strpos($name, "=")) !== false) {
				$val = substr($name, $pos+1);
				$name = substr($name, 0, $pos);
				if (!empty($name) && isset($this->opts[$name])) {
					$type = $this->opts[$name]['t'];
					if ($type == self::TYPE_BOOL) {
						$val = true;
					}
					$this->parseOption($type, $name, $val);
				}
				continue;
			}

			// unknown option, ignored
			if (!isset($this->opts[$name])) {
				continue;
			}

			$type = $this->opts[$name]['t'];
			switch ($type) {
				case self::TYPE_BOOL:
					$val = true;
					break;
				case self::TYPE_INT:
				case self::TYPE_FLOAT:
				case self::TYPE_STRING:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
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

	private function parseOption($type, $name, $val): bool {
		switch ($type) {
		case self::TYPE_BOOL:
			$this->parsedOpts[$name] = $val;
			return false;
		case self::TYPE_INT:
			$val = $this->isNumeric($val);
			if ($val !== false && is_int($val)) {
				$this->parsedOpts[$name] = $val;
				return true;
			}
			throw new Exception(sprintf("option[%s] require int value.", $name));
		case self::TYPE_FLOAT:
			$val = $this->isNumeric($val);
			if ($val !== false && is_float($val) ) {
				$this->parsedOpts[$name] = $val;
				return true;
			}
			throw new Exception(sprintf("option[%s] require float value.", $name));
		case self::TYPE_STRING:
			$check = $this->isValidOption($val);
			if ($check === false) {
				$this->parsedOpts[$name] = $val;
				return true;
			}
			throw new Exception(sprintf("option[%s] require string value.", $name));
		default:
			return true;
		}
	}

	private function isValidOption(string $opt) {
		$opt = trim($opt);
		$len = strlen($opt);
		if (empty($opt) || $len <= 1) {
			return false;
		}

		if ($this->strEqual($opt, '--', 2)) {
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

	private function strEqual(string $str1, string $str2, int $length): bool {
		return strncmp($str1, $str2, $length) === 0;
	}

	private function isNumeric(string $str) {
		if (is_numeric($str)) {
			return $str + 0;
		}
		return false;
	}

	public function offsetExists($key): bool {
		return isset($this->opts[$key]);
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
