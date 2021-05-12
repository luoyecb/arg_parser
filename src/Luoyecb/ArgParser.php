<?php
namespace Luoyecb;

use \Exception;

// A simple command line option parser.
class ArgParser {
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
			if ($argv[$idx] == '--') {
				$this->args = array_slice($argv, $idx+1);
				break;
			}

			$name = $this->isValidOption($argv[$idx]);
			if ($name === false) {
				$this->args = array_slice($argv, $idx);
				break;
			} else {
				$idx++;
				if (!isset($this->opts[$name])) {
					// unknown option, ignored
					continue;
				}

				$type = $this->opts[$name]['t'];
				switch ($type) {
				case self::TYPE_BOOL:
					$this->parsedOpts[$name] = true;
					break;
				case self::TYPE_INT:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
					}

					$val = $this->isNumeric($argv[$idx]);
					if ($val !== false && is_int($val)) {
						$this->parsedOpts[$name] = $val;
						$idx++;
					} else {
						throw new Exception(sprintf("option[%s] require int value.", $name));
					}
					break;
				case self::TYPE_FLOAT:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
					}

					$val = $this->isNumeric($argv[$idx]);
					if ($val !== false && is_float($val) ) {
						$this->parsedOpts[$name] = $val;
						$idx++;
					} else {
						throw new Exception(sprintf("option[%s] require float value.", $name));
					}
					break;
				case self::TYPE_STRING:
					if ($idx >= $len) {
						throw new Exception(sprintf("option[%s] require value.", $name));
					}

					$val = $this->isValidOption($argv[$idx]);
					if ($val === false) {
						$this->parsedOpts[$name] = $argv[$idx];
						$idx++;
					} else {
						throw new Exception(sprintf("option[%s] require string value.", $name));
					}
				}
			}
		}

		$this->isParsed = true;
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
}
