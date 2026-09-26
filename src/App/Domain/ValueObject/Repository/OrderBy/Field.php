<?php

declare(strict_types=1);

namespace App\Domain\ValueObject\Repository\OrderBy;

use InvalidArgumentException;

use function implode;
use function in_array;
use function mb_strtoupper;
use function preg_match;
use function sprintf;

final class Field
{
    private const DIRECTIONS = [
        'ASC',
        'DESC',
    ];

    private const NAME_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

    private string $name;

    private string $direction;

    public function __construct(string $name, string $direction = 'ASC')
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Name can not be empty');
        }

        if (! preg_match(self::NAME_PATTERN, $name)) {
            throw new InvalidArgumentException(
                sprintf('Name "%s" is not a valid field name', $name)
            );
        }

        $direction = mb_strtoupper($direction);
        if (! in_array($direction, self::DIRECTIONS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Direction "%s" is invalid. Valid directions are %s',
                    $direction,
                    implode(', ', self::DIRECTIONS)
                )
            );
        }

        $this->name      = $name;
        $this->direction = $direction;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function direction(): string
    {
        return $this->direction;
    }
}
