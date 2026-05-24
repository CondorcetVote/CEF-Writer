<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator\Parameter;

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

/**
 * `#/Number of Seats:` parameter — strictly positive integer.
 */
final class NumberOfSeatsParameter implements ParameterInterface
{
    /**
     * @throws CefFormatException
     */
    public function __construct(public readonly int $seats)
    {
        if ($seats < 1) {
            throw new CefFormatException('Number of seats must be a positive integer.');
        }
    }

    public function getName(): string
    {
        return StandardParameter::NumberOfSeats->value;
    }

    public function getFormattedValue(bool $autoFormat = true): string
    {
        return (string) $this->seats;
    }
}
