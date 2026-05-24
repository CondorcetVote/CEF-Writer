<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator\Parameter;

use CondorcetVote\CondorcetElectionFormatGenerator\CefFormat;
use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

/**
 * Free-form parameter for tooling that extends CEF with project-specific keys.
 *
 * The name must avoid every reserved character and `:`, since `:` separates
 * a parameter from its value. The value must avoid line breaks but is
 * otherwise free-form (the spec only reserves characters for *structured*
 * values).
 */
final class CustomParameter implements ParameterInterface
{
    public readonly string $name;

    public readonly string $value;

    /**
     * @throws CefFormatException
     */
    public function __construct(string $name, string $value)
    {
        $trimmedName = trim($name);

        if ($trimmedName === '') {
            throw new CefFormatException('Custom parameter name cannot be empty.');
        }

        if (str_contains($trimmedName, ':')) {
            throw new CefFormatException('Custom parameter name cannot contain ":".');
        }

        CefFormat::assertValueIsClean($trimmedName, 'Custom parameter name');
        CefFormat::assertNoReservedNorLineBreak($value, 'Custom parameter value');

        $this->name = $trimmedName;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFormattedValue(bool $autoFormat = true): string
    {
        return $this->value;
    }
}
