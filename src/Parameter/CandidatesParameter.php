<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Parameter;

use CondorcetVote\CefWriter\CefFormat;
use CondorcetVote\CefWriter\Exception\{CefFormatException, DuplicateCandidateException, InvalidValueException};

/**
 * `#/Candidates:` parameter — declares the official list of candidates.
 *
 * Candidate names are written separated by `;`. With auto-format on, the
 * separator is padded with spaces (`A ; B ; C`) for readability; otherwise
 * the most compact form (`A;B;C`) is used.
 */
final class CandidatesParameter implements ParameterInterface
{
    /** @var list<string> */
    public readonly array $candidates;

    /**
     * @param list<string> $candidates non-empty list of distinct candidate names
     *
     * @throws CefFormatException
     */
    public function __construct(array $candidates)
    {
        if (\count($candidates) === 0) {
            throw new InvalidValueException('Candidates list cannot be empty.');
        }

        $seen = [];

        foreach ($candidates as $candidate) {
            $trimmed = trim($candidate);
            CefFormat::assertValueIsClean($trimmed, 'Candidate name');

            if (isset($seen[$trimmed])) {
                throw new DuplicateCandidateException(\sprintf('Duplicate candidate "%s".', $trimmed));
            }

            $seen[$trimmed] = true;
        }

        $this->candidates = array_map(\trim(...), $candidates);
    }

    public function getName(): string
    {
        return StandardParameter::Candidates->value;
    }

    public function getFormattedValue(bool $autoFormat = true): string
    {
        return implode($autoFormat ? ' ; ' : ';', $this->candidates);
    }
}
