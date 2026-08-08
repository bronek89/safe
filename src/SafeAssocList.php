<?php declare(strict_types=1);

namespace GW\Safe;

use Countable;
use function array_filter;
use function array_map;
use function array_values;
use function count;
use function is_array;

final class SafeAssocList implements Countable
{
	/** @var list<SafeAssocArray> */
	private array $items;

	private function __construct(SafeAssocArray ...$items)
	{
		// spreading a string-keyed array passes named arguments, which land in
		// the variadic keyed by name
		$this->items = array_values($items);
	}

	public static function from(SafeAssocArray ...$items): self
	{
		return new self(...$items);
	}

	/** @param array<mixed> $items non-array items are skipped */
	public static function fromArray(array $items): self
	{
		return self::from(
			...array_map(SafeAssocArray::from(...), array_values(array_filter($items, is_array(...))))
		);
	}

	/** @return list<SafeAssocArray> */
	public function toArray(): array
	{
		return $this->items;
	}

	public function first(): ?SafeAssocArray
	{
		return $this->items[0] ?? null;
	}

	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * @template T
	 * @phpstan-param callable(SafeAssocArray):T $map
	 * @phpstan-return list<T>
	 */
	public function map(callable $map): array
	{
		return array_map($map, $this->items);
	}

	/**
	 * @phpstan-param callable(SafeAssocArray):bool $filter
	 */
	public function filter(callable $filter): self
	{
		return new self(...array_values(array_filter($this->items, $filter)));
	}
}
