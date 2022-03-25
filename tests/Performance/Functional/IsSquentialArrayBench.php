<?php
namespace Encase\Performance;

use Encase\Functional\Str;
use Encase\Functional\Func;
use function Encase\Functional\fill;
use function Encase\Functional\slice;
use function Encase\Functional\union;

/**
 * @BeforeClassMethods({"initArrays"})
 */
class IsSequentialArrayBench
{
	/** @var string[] */
	private static $smallSequentialArray = [];

	/** @var string[] */
	private static $largeSequentialArray = [];

	/** @var string[] */
	private static $smallIndexedArray = [];

	/** @var string[] */
	private static $largeIndexedArray = [];

	/** @var string[] */
	private static $smallKeyedArrayKeyMiddle = [];

	/** @var string[] */
	private static $largeKeyedArrayKeyMiddle = [];

	/** @var string[] */
	private static $smallKeyedArrayKeyEnd = [];

	/** @var string[] */
	private static $largeKeyedArrayKeyEnd = [];

	public static function initArrays()
	{
		$generateElement = function () {
			while (true) {
				yield Str::random()->get() => Str::random()->get();
			}
		};

		$smallArraySize = 6;
		$largeArraySize = 500;
		self::$smallSequentialArray = fill([], Func::new($generateElement()), $smallArraySize);
		self::$largeSequentialArray = fill([], Func::new($generateElement()), $largeArraySize);
		self::$smallIndexedArray = self::$smallSequentialArray;
		self::$largeIndexedArray = self::$smallSequentialArray;
		\shuffle(self::$smallIndexedArray);
		\shuffle(self::$largeIndexedArray);
		self::$smallKeyedArrayKeyMiddle = union(
			slice(self::$smallIndexedArray, 0, $smallArraySize / 2),
			['a' => 42],
			slice(self::$smallIndexedArray, $smallArraySize / 2)
		);
		self::$largeKeyedArrayKeyMiddle = union(
			slice(self::$largeIndexedArray, 0, $smallArraySize / 2),
			['a' => 42],
			slice(self::$largeIndexedArray, $smallArraySize / 2)
		);
		self::$smallKeyedArrayKeyEnd = union(
			self::$smallIndexedArray,
			['a' => 42]
		);
		self::$largeKeyedArrayKeyEnd = union(
			self::$largeIndexedArray,
			['a' => 42]
		);
	}

	public function provideArrays()
	{
		yield 'SmSeq' => ['array' => self::$smallSequentialArray];
		yield 'LgSeq' => ['array' => self::$largeSequentialArray];
		yield 'SmIdx' => ['array' => self::$smallIndexedArray];
		yield 'LgIdx' => ['array' => self::$largeIndexedArray];
		yield 'SmAssocMid' => ['array' => self::$smallKeyedArrayKeyMiddle];
		yield 'LgAssocMid' => ['array' => self::$largeKeyedArrayKeyMiddle];
		yield 'SmAssocEnd' => ['array' => self::$smallKeyedArrayKeyEnd];
		yield 'LgAssocEnd' => ['array' => self::$largeKeyedArrayKeyEnd];
	}


	/**
	 * @ParamProviders({"provideArrays"})
	 * @Revs(1000)
	 * @Iterations(4)
	 */
	public function benchMethodArrayKeys($params)
	{
		$keys = \array_keys($params['array']);
		return $keys === \array_keys($keys);
	}

	/**
	 * @ParamProviders({"provideArrays"})
	 * @Revs(1000)
	 * @Iterations(4)
	 */
	public function benchMethodForEachIndexing($params)
	{
		$index = 0;

		foreach (\array_keys($params['array']) as $key) {
			// For some reason, using an incrementing index to compare to is a bit
			// slower than the "double array keys" method, but subtracting the
			// current and previous keys and comparing the result against 1 speeds
			// up the loop by roughly 200%.
			if (!\is_int($key) || $key === $index) {
				return false;
			}

			++$index;
		}

		return true;
	}

	/**
	 * @ParamProviders({"provideArrays"})
	 * @Revs(1000)
	 * @Iterations(4)
	 */
	public function benchMethodForEachLastKeyDiff($params)
	{
		$lastKey = -1;

		foreach (\array_keys($params['array']) as $key) {
			if (!\is_int($key) || ($key - $lastKey) != 1) {
				return false;
			}

			$lastKey = $key;
		}

		return true;
	}
}
