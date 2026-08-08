<?php

namespace tests\GW\Safe;

use GW\Safe\SafeAssocArray;
use GW\Safe\SafeAssocList;
use PHPUnit\Framework\TestCase;

class SafeAssocListTest extends TestCase
{
	function test_from(): void
	{
		$john = SafeAssocArray::from(['name' => 'John']);
		$mary = SafeAssocArray::from(['name' => 'Mary']);
		$list = SafeAssocList::from($john, $mary);

		self::assertEquals([$john, $mary], $list->toArray());
	}

	function test_to_array(): void
	{
		$arrayList = SafeAssocList::fromArray([['name' => 'John'], ['name' => 'Mary']])->toArray();

		self::assertContainsOnlyInstancesOf(SafeAssocArray::class, $arrayList);
		self::assertEquals('John', $arrayList[0]->string('name'));
		self::assertEquals('Mary', $arrayList[1]->string('name'));
		self::assertEquals('???', $arrayList[1]->string('age', '???'));
	}

	function test_count(): void
	{
		self::assertCount(3, SafeAssocList::fromArray([[], [], []]));
	}

	function test_first(): void
	{
		$john = SafeAssocArray::from(['name' => 'John']);
		$mary = SafeAssocArray::from(['name' => 'Mary']);

		self::assertSame($john, SafeAssocList::from($john, $mary)->first());
	}

	function test_first_returns_null_when_empty(): void
	{
		self::assertNull(SafeAssocList::from()->first());
	}

	function test_first_after_filter(): void
	{
		$list = SafeAssocList::fromArray([['name' => 'John'], ['name' => 'Mary']])
			->filter(fn(SafeAssocArray $person): bool => $person->string('name') === 'Mary');

		self::assertEquals('Mary', $list->first()?->string('name'));
	}

	function test_keeps_list_keys_when_built_from_string_keyed_array(): void
	{
		$list = SafeAssocList::fromArray(['a' => ['name' => 'John'], 'b' => ['name' => 'Mary']]);

		self::assertSame([0, 1], array_keys($list->toArray()));
		self::assertEquals('John', $list->first()?->string('name'));
	}

	function test_filter(): void
	{
		$list = SafeAssocList::fromArray(
			[
				['name' => 'John', 'age' => 30],
				['name' => 'Mary', 'age' => 28],
				['name' => 'Kevin', 'age' => 8]
			]
		)->filter(fn(SafeAssocArray $person): bool => $person->int('age', 0) > 18);

		self::assertCount(2, $list);
	}

	function test_map(): void
	{
		$names = SafeAssocList::fromArray(
			[
				['name' => 'John', 'age' => 30],
				['name' => 'Mary', 'age' => 28],
				['age' => 0],
			]
		)->map(fn(SafeAssocArray $person): string => $person->string('name', 'unknown'));

		self::assertEquals(['John', 'Mary', 'unknown'], $names);
	}
}
