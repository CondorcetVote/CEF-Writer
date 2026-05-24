<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator;

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

/**
 * Internal helpers shared across the writer and the value objects.
 *
 * The class is final and stateless: it only exposes static utilities that
 * encode rules from the CEF specification (reserved characters, blank-ballot
 * sentinel, etc.).
 *
 * @internal
 */
final class CefFormat
{
    /**
     * Characters that the specification reserves for syntactic use and that
     * therefore must never appear inside any user-supplied value.
     *
     * @var list<string>
     */
    public const array RESERVED_CHARACTERS = ['>', '=', ';', ',', '#', '/', '*', '^'];

    /**
     * Sentinel value emitted as the whole ranking of a blank ballot.
     */
    public const string EMPTY_RANKING = '/EMPTY_RANKING/';

    /**
     * Tag/ranking separator on a vote line.
     */
    public const string TAGS_SEPARATOR = '||';

    /**
     * Reject any value that contains a reserved character or a line break.
     *
     * @throws CefFormatException
     */
    public static function assertValueIsClean(string $value, string $context): void
    {
        if ($value === '') {
            throw new CefFormatException(\sprintf('%s cannot be empty.', $context));
        }

        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new CefFormatException(\sprintf('%s cannot contain a line break.', $context));
        }

        foreach (self::RESERVED_CHARACTERS as $reserved) {
            if (str_contains($value, $reserved)) {
                throw new CefFormatException(\sprintf(
                    '%s cannot contain the reserved character "%s".',
                    $context,
                    $reserved,
                ));
            }
        }
    }

    /**
     * Inline comments are free-form text but must stay on a single line.
     *
     * @throws CefFormatException
     */
    public static function assertSingleLine(string $value, string $context): void
    {
        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new CefFormatException(\sprintf('%s cannot contain a line break.', $context));
        }
    }
}
