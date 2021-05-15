<?php
namespace Luoyecb;

class StringUtil {

	public static function contains(string $str, string $substr): bool {
		return strpos($str, $substr) !== false;
	}

	public static function containsAny(string $str, string ...$substrs): bool {
		foreach ($substrs as $v) {
			if (self::contains($str, $v)) {
				return true;
			}
		}
		return false;
	}

	public static function containsAll(string $str, string ...$substrs): bool {
		foreach ($substrs as $v) {
			if (!self::contains($str, $v)) {
				return false;
			}
		}
		return true;
	}

	public static function containsFirstPos(string $str, string $substr) {
		return strpos($str, $substr);
	}

	public static function equalsLen(string $str1, string $str2, int $length): bool {
		return strncmp($str1, $str2, $length) === 0;
	}

}
