<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\StandardParameter;

it('maps each case to the exact spec parameter name', function (): void {
    expect(StandardParameter::Candidates->value)->toBe('Candidates');
    expect(StandardParameter::NumberOfSeats->value)->toBe('Number of Seats');
    expect(StandardParameter::ImplicitRanking->value)->toBe('Implicit Ranking');
    expect(StandardParameter::VotingMethods->value)->toBe('Voting Methods');
    expect(StandardParameter::WeightAllowed->value)->toBe('Weight Allowed');
});

it('exposes exactly the five standard parameters', function (): void {
    expect(StandardParameter::cases())->toHaveCount(5);
});
