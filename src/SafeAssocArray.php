<?php declare(strict_types=1);

namespace GW\Safe;

use function array_key_exists;

final class SafeAssocArray implements SafeAccessor
{
    use SafeAccessorTrait;

    /** @var array<string|int, mixed> */
    private array $array;

    /** @param array<string|int, mixed> $array */
    private function __construct(array $array)
    {
        $this->array = $array;
    }

    /** @param array<string|int, mixed> $array */
    public static function from(array $array): self
    {
        return new self($array);
    }

    /**
     * @return mixed
     */
    public function value(string $key, $default)
    {
        if (array_key_exists($key, $this->array)) {
            return $this->array[$key];
        }

        return $default;
    }
}
