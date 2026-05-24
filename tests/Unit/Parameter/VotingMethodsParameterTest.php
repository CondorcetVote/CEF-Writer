<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\{InvalidValueException, ReservedCharacterException};
use CondorcetVote\CefWriter\Parameter\VotingMethodsParameter;

it('joins method names with semicolons', function (): void {
    $param = new VotingMethodsParameter(['Schulze', 'Copeland', 'Ranked Pairs']);

    expect($param->getName())->toBe('Voting Methods');
    expect($param->getFormattedValue(false))->toBe('Schulze;Copeland;Ranked Pairs');
    expect($param->getFormattedValue(true))->toBe('Schulze ; Copeland ; Ranked Pairs');
});

it('rejects an empty methods list', function (): void {
    new VotingMethodsParameter([]);
})->throws(InvalidValueException::class);

it('rejects a method name with a reserved character', function (): void {
    new VotingMethodsParameter(['Schulze', 'Copeland;Variant']);
})->throws(ReservedCharacterException::class);
