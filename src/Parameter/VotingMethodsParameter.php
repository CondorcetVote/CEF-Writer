<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator\Parameter;

use CondorcetVote\CondorcetElectionFormatGenerator\CefFormat;
use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

/**
 * `#/Voting Methods:` parameter — list of method identifiers separated by `;`.
 */
final class VotingMethodsParameter implements ParameterInterface
{
    /** @var list<string> */
    public readonly array $methods;

    /**
     * @param list<string> $methods non-empty list of method names
     *
     * @throws CefFormatException
     */
    public function __construct(array $methods)
    {
        if (\count($methods) === 0) {
            throw new CefFormatException('Voting methods list cannot be empty.');
        }

        foreach ($methods as $method) {
            CefFormat::assertValueIsClean(trim($method), 'Voting method name');
        }

        $this->methods = array_map(\trim(...), $methods);
    }

    public function getName(): string
    {
        return StandardParameter::VotingMethods->value;
    }

    public function getFormattedValue(bool $autoFormat = true): string
    {
        return implode($autoFormat ? ' ; ' : ';', $this->methods);
    }
}
