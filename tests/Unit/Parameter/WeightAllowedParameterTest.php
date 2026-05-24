<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\WeightAllowedParameter;

it('renders the standard boolean tokens', function (): void {
    $on = new WeightAllowedParameter(true);
    $off = new WeightAllowedParameter(false);

    expect($on->getName())->toBe('Weight Allowed');
    expect($on->getFormattedValue(true))->toBe('true');
    expect($off->getFormattedValue(true))->toBe('false');
});
