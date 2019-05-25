<?php
namespace Encase\DB;

use ReflectionClass;

class Model
{
	public static function createTable()
	{
		$class = new ReflectionClass(static::class);
		$columns = [];
		$defaults = $class->getDefaultProperties();
		foreach ($class->getProperties() as $property) {
			$comment = $property->getDocComment();

			echo $comment."\n";

			if (preg_match_all('/(?!\s*\*\s*)@Column\s+((.(?!(\s\*\s@)))+)/s', $comment, $matches)) {
				foreach ($matches[1] as $match) {
					$match = trim(preg_replace('/\s*\*\s*/', ' ', $match));
					if (strlen($match) >= 2 && $match[0] === '(' && $match[-1] === ')') {
						$match = substr($match, 1, -1);
					}

					preg_match_all('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/s', $match, $split);
					die(print_r($split, 1));

					$inQuote = false;
					$backslash = false;
					$str = '';
					$blahs = [];

					for ($i = 0; $i < strlen($match); ++$i) {
						$char = $match[$i];

						if ($char === '\\') {
							if ($inQuote && !$backslash) {
								$backslash = true;
								continue;
							}
						} elseif ($char === ',' && !$inQuote) {
							$blahs[] = $str;
							continue;
						}

						$str .= $char;
					}

					if (empty($blahs)) {
						$blahs[] = $str;
					}

					var_dump($blahs);
				}
			}

			// var_dump($columns);

			/*$name = $property->getName();
			$default = $defaults[$name];
			$columns[$name] = compact('default', 'comment');*/
		}

		// var_dump($columns);
		// var_dump($class);
	}
}
