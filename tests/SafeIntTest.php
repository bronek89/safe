<?php

namespace tests\GW\Safe;

use GW\Safe\SafeAssocArray;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;
use function random_int;

class SafeIntTest extends TestCase
{
    #[DataProvider('possibleIntegerValues')]
    function test_casting_possible_int_values(mixed $value, int $expected): void
    {
        self::assertEquals($expected, SafeAssocArray::from(['int' => $value])->int('int'));
        self::assertEquals($expected, SafeAssocArray::from(['int' => $value])->intNullable('int'));
        self::assertEquals($expected, SafeAssocArray::from(['int' => $value])->intOrNull('int'));
        self::assertEquals($expected, SafeAssocArray::from(['int' => $value])->intOrDefault('int', random_int(0, 99)));
    }

    #[DataProvider('impossibleIntegerValues')]
    function test_throwing_InvalidArgumentException_on_value_that_cannot_be_int(mixed $notInt): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeAssocArray::from(['value' => $notInt])->int('value');
    }

    #[DataProvider('impossibleIntegerValues')]
    function test_throwing_InvalidArgumentException_on_value_that_cannot_be_int_nullable(mixed $notInt): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeAssocArray::from(['value' => $notInt])->intNullable('value');
    }

    #[DataProvider('impossibleIntegerValues')]
    function test_intOrNull_returns_null_on_value_that_cannot_be_int(mixed $notInt): void
    {
        self::assertNull(SafeAssocArray::from(['value' => $notInt])->intOrNull('value'));
    }

    function test_throwing_InvalidArgumentException_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeAssocArray::from(['value' => null])->int('value');
    }

    #[DataProvider('impossibleIntegerValues')]
    function test_returning_default_when_value_is_not_numeric(mixed $notInt): void
    {
        $safe = SafeAssocArray::from(['value' => $notInt]);
        $int = random_int(0, 1000000);

        self::assertSame($int, $safe->intOrDefault('value', $int));
    }

    function test_casts_array_of_values_that_can_be_int(): void
    {
        self::assertEquals(
            [123, 1, 1, 0, 123],
            SafeAssocArray::from(
                [
                    'ints' => [
                        123,
                        1.23,
                        true,
                        false,
                        new StringObject('123'),
                    ],
                ]
            )->ints('ints')
        );
    }

    function test_casting_associative_array_discards_keys(): void
    {
        self::assertSame(
            [123, 456],
            SafeAssocArray::from(['ints' => ['a' => '123', 'b' => '456']])->ints('ints')
        );
    }

    function test_throws_InvalidArgumentException_when_cannot_cast_array_of_ints(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeAssocArray::from(['notOnlyInts' => ['123', new stdClass()]])->ints('notOnlyInts');
    }

    function test_filtered_casting_only_ints_from_array_of_mixed_values(): void
    {
        self::assertEquals(
            [123, 456],
            SafeAssocArray::from(
                [
                    'ints' => [
                        '123',
                        ['array'],
                        new StringObject('456'),
                        null,
                    ],
                ]
            )->intsFiltered('ints')
        );
    }

    function test_casting_array_of_mixed_values_with_default(): void
    {
        $safe = SafeAssocArray::from(
            [
                'ints' => [
                    '123',
                    ['array'],
                    new StringObject('456'),
                    null,
                ],
            ]
        );

        self::assertEquals([123, 0, 456, 0], $safe->intsForced('ints'));
        self::assertEquals([123, 42, 456, 42], $safe->intsForced('ints', 42));
    }

    /** @return list<array{mixed, int}> */
    public static function possibleIntegerValues(): array
    {
        return [
            [123, 123],
            [1.23, 1],
            [true, 1],
            [false, 0],
            [new StringObject('123'), 123],
        ];
    }

    /** @return list<array{mixed}> */
    public static function impossibleIntegerValues(): array
    {
        return [
            [['array']],
            ['string'],
            [new StringObject('string')],
        ];
    }
}
