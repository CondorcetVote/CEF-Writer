<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Parameter;

/**
 * `#/Implicit Ranking:` parameter — boolean toggle.
 */
final class ImplicitRankingParameter implements ParameterInterface
{
    public function __construct(public readonly bool $enabled) {}

    public function getName(): string
    {
        return StandardParameter::ImplicitRanking->value;
    }

    public function getFormattedValue(bool $autoFormat = true): string
    {
        return $this->enabled ? 'true' : 'false';
    }
}
