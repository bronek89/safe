<?php

namespace tests\GW\Safe;

use GW\Safe\SafeAssocArray;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SafeBoolTest extends TestCase
{
    #[DataProvider('possibleBooleanValues')]
    function test_casting_possible_bool_values(mixed $value, bool $expected): void
    {
        self::assertEquals($expected, SafeAssocArray::from(['bool' => $value])->bool('bool'));
    }

    #[DataProvider('impossibleBooleanValues')]
    function test_throwing_InvalidArgumentException_on_value_that_cannot_be_bool(mixed $notBool): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeAssocArray::from(['value' => $notBool])->bool('value');
    }

    function test_throwing_InvalidArgumentException_on_null(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeAssocArray::from(['value' => null])->bool('value');
    }

    #[DataProvider('impossibleBooleanValues')]
    function test_returning_default_when_value_is_not_bool(mixed $notBool): void
    {
        $safe = SafeAssocArray::from(['value' => $notBool]);

        self::assertTrue($safe->boolOrDefault('value', true));
        self::assertFalse($safe->boolOrDefault('value', false));
    }

    /** @return list<array{mixed, bool}> */
    public static function possibleBooleanValues(): array
    {
        return [
            [true, true],
            [false, false],
            [1, true],
            [0, false],
            ['1', true],
            ['0', false],
        ];
    }

    /** @return list<array{mixed}> */
    public static function impossibleBooleanValues(): array
    {
        return [
            ['YES'],
            ['x'],
            [12],
            [['array']],
            ['string'],
            [new StringObject('string')],
            [null],
        ];
    }
}
