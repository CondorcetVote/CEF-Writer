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
     * Empty strings are rejected too — this helper is meant for *required*
     * structural values (names, tags, candidate labels, …).
     *
     * @throws CefFormatException
     */
    public static function assertValueIsClean(string $value, string $context): void
    {
        if ($value === '') {
            throw new CefFormatException(\sprintf('%s cannot be empty.', $context));
        }

        self::assertNoReservedNorLineBreak($value, $context);
    }

    /**
     * Reject any value that contains a reserved character, a line break, a
     * null byte, or an invalid UTF-8 byte sequence. Accept the empty string.
     * Use for optionally-empty value strings (e.g. a custom parameter's
     * free-form value).
     *
     * @throws CefFormatException
     */
    public static function assertNoReservedNorLineBreak(string $value, string $context): void
    {
        self::assertSafeText($value, $context);

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
     * Inline comments are free-form text but must stay on a single line and
     * contain only valid UTF-8 (with no null byte).
     *
     * @throws CefFormatException
     */
    public static function assertSingleLine(string $value, string $context): void
    {
        self::assertSafeText($value, $context);
    }

    /**
     * Verify that `$value` is valid UTF-8, single-line, and contains no null
     * byte. Shared base for every value-bound assertion above.
     *
     * UTF-8 validity is delegated to `mb_check_encoding()` (ext-mbstring is a
     * declared runtime dependency of this library) — the C-level scanner is
     * faster than a `preg_match('//u', $value)` round-trip through PCRE.
     *
     * @throws CefFormatException
     */
    private static function assertSafeText(string $value, string $context): void
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            throw new CefFormatException(\sprintf(
                '%s contains an invalid UTF-8 byte sequence.',
                $context,
            ));
        }

        if (preg_match('/[\r\n]/', $value) === 1) {
            throw new CefFormatException(\sprintf('%s cannot contain a line break.', $context));
        }

        if (str_contains($value, "\0")) {
            throw new CefFormatException(\sprintf('%s cannot contain a null byte.', $context));
        }
    }
}
