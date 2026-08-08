<?php

namespace tests\GW\Safe;

use GW\Safe\SafeAssocArray;
use PHPUnit\Framework\TestCase;
use stdClass;

class SafeArrayAccessTest extends TestCase
{
    function test_unwraps_to_plain_array(): void
    {
        $array = ['name' => 'John', 'age' => 18];

        self::assertSame($array, SafeAssocArray::from($array)->toArray());
    }

    function test_unwraps_empty_array(): void
    {
        self::assertSame([], SafeAssocArray::from([])->toArray());
    }

    function test_reads_raw_array(): void
    {
        $safe = SafeAssocArray::from(['extra' => ['a' => 1, 'b' => 2]]);

        self::assertSame(['a' => 1, 'b' => 2], $safe->rawArray('extra'));
    }

    function test_raw_array_keeps_keys_and_nesting(): void
    {
        $nested = ['list' => [1, 2], 'deep' => ['x' => ['y' => 'z']]];

        self::assertSame($nested, SafeAssocArray::from(['extra' => $nested])->rawArray('extra'));
    }

    function test_raw_array_falls_back_to_default_for_missing_key(): void
    {
        $safe = SafeAssocArray::from([]);

        self::assertSame([], $safe->rawArray('extra'));
        self::assertSame(['fallback'], $safe->rawArray('extra', ['fallback']));
    }

    /**
     * Unlike an (array) cast, a non-array value never gets wrapped into a
     * single-element array or exploded into object properties.
     */
    function test_raw_array_falls_back_to_default_for_non_array_value(): void
    {
        foreach (['string', 123, 1.5, true, null, new stdClass()] as $value) {
            self::assertSame(['fallback'], SafeAssocArray::from(['extra' => $value])->rawArray('extra', ['fallback']));
        }
    }

    function test_has_key_with_value(): void
    {
        $safe = SafeAssocArray::from(['name' => 'John', 'age' => 0, 'tags' => [], 'flag' => false]);

        self::assertTrue($safe->has('name'));
        self::assertTrue($safe->has('age'));
        self::assertTrue($safe->has('tags'));
        self::assertTrue($safe->has('flag'));
    }

    function test_has_is_false_for_missing_key(): void
    {
        self::assertFalse(SafeAssocArray::from([])->has('name'));
    }

    function test_has_treats_null_as_absent(): void
    {
        self::assertFalse(SafeAssocArray::from(['name' => null])->has('name'));
    }
}
