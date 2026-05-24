<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\InvalidValueException;
use CondorcetVote\CefWriter\Parameter\NumberOfSeatsParameter;

it('emits the integer as its formatted value', function (): void {
    $param = new NumberOfSeatsParameter(42);

    expect($param->getName())->toBe('Number of Seats');
    expect($param->getFormattedValue(false))->toBe('42');
    expect($param->getFormattedValue(true))->toBe('42');
});

it('accepts one seat', function (): void {
    expect(new NumberOfSeatsParameter(1)->seats)->toBe(1);
});

it('rejects zero seats', function (): void {
    new NumberOfSeatsParameter(0);
})->throws(InvalidValueException::class, 'positive');

it('rejects a negative number of seats', function (): void {
    new NumberOfSeatsParameter(-3);
})->throws(InvalidValueException::class, 'positive');
