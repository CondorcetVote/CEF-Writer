<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\ImplicitRankingParameter;

it('renders true as the literal "true"', function (): void {
    $param = new ImplicitRankingParameter(true);

    expect($param->getName())->toBe('Implicit Ranking');
    expect($param->getFormattedValue(false))->toBe('true');
    expect($param->getFormattedValue(true))->toBe('true');
});

it('renders false as the literal "false"', function (): void {
    expect(new ImplicitRankingParameter(false)->getFormattedValue(false))->toBe('false');
});
