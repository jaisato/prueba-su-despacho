<?php

declare(strict_types=1);

namespace App\Domain\Exception\ValueObject\Repository;

use Exception;

final class DateRangeIsNotValid extends Exception
{
    /**
     * @return DateRangeIsNotValid
     */
    public static function becauseBothValuesAreMandatory(): self
    {
        return new self(
            'Both values are mandatory'
        );
    }

    /**
     * @return DateRangeIsNotValid
     */
    public static function becauseToDateIsEarlierThanFromDate(): self
    {
        return new self(
            'The `to` date may not be earlier than `from` date'
        );
    }
}
