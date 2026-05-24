<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Parameter;

/**
 * `#/Weight Allowed:` parameter — boolean toggle.
 */
final class WeightAllowedParameter implements ParameterInterface
{
    public function __construct(public readonly bool $enabled) {}

    public function getName(): string
    {
        return StandardParameter::WeightAllowed->value;
    }

    public function getFormattedValue(bool $autoFormat = true): string
    {
        return $this->enabled ? 'true' : 'false';
    }
}
