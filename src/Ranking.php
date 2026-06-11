<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter;

use CondorcetVote\CefWriter\Exception\{CefFormatException, DuplicateCandidateException, InvalidValueException};

/**
 * An ordered ranking of candidates.
 *
 * The ranking is expressed as an ordered list of ranks; each rank is itself a
 * list of candidate names tied at that position. An empty top-level ranking
 * (`[]`) renders as the `/EMPTY_RANKING/` blank-ballot sentinel.
 *
 * A `Ranking` is immutable and self-validating: any specification violation
 * (reserved character, empty rank, duplicate candidate) throws a
 * {@see CefFormatException} at construction time. Render it to a CEF string
 * with {@see format()} (or by casting to `string`).
 */
final class Ranking
{
    /** @var list<list<string>> */
    public readonly array $ranks;

    /**
     * @param list<list<string>> $ranks Ordered ranks; each inner list is non-empty.
     *                                  Pass `[]` for the `/EMPTY_RANKING/` blank ballot.
     *
     * @throws CefFormatException on any specification violation
     */
    public function __construct(array $ranks)
    {
        $this->ranks = self::validate($ranks);
    }

    /**
     * Build a {@see Ranking} from a ranking-only string.
     *
     * The string may contain candidate names joined by the `>` (rank) and `=`
     * (tie) operators, or the `/EMPTY_RANKING/` sentinel. Any reserved
     * character (`^`, `*`, `#`, `;`, `,`, `/`), the `||` tag separator, or a
     * line break is rejected — there is no way to smuggle a weight,
     * quantifier, tag or inline comment through the string.
     *
     * @param string $ranking Ranking only, e.g. `"A > B = C"` or `"/EMPTY_RANKING/"`.
     *
     * @throws CefFormatException
     */
    public static function fromString(string $ranking): self
    {
        $work = trim($ranking);

        if ($work === '') {
            throw new InvalidValueException(
                'Ranking string cannot be empty; use "/EMPTY_RANKING/" for a blank ballot.',
            );
        }

        // The "||" tag separator is the only forbidden pattern that
        // per-candidate validation would not catch on its own ("|" is not a
        // reserved character), so reject it explicitly here. Every reserved
        // character and line break is rejected later by validate().
        CefFormat::assertNoTagSeparator($work, 'Ranking');

        return new self(self::split($work));
    }

    /**
     * Render the ranking — *without* trailing newline, tags, weight,
     * quantifier or inline comment — using the spacing flavor selected by
     * `$autoFormat`.
     *
     * When `$autoFormat` is `true` (default), ranks are separated by `" > "`
     * and tied candidates by `" = "`; when `false`, the most compact `>` / `=`
     * form is emitted. An empty ranking yields the `/EMPTY_RANKING/` sentinel.
     */
    public function format(bool $autoFormat = true): string
    {
        if (\count($this->ranks) === 0) {
            return CefFormat::EMPTY_RANKING;
        }

        $rankSep = $autoFormat ? ' > ' : '>';
        $tieSep = $autoFormat ? ' = ' : '=';

        $ranks = array_map(
            static fn(array $rank): string => implode($tieSep, $rank),
            $this->ranks,
        );

        return implode($rankSep, $ranks);
    }

    /**
     * Render the ranking in its relaxed (auto-formatted) flavor.
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * Split a cleaned ranking string into its raw, *un-validated* rank/tie
     * structure. The `/EMPTY_RANKING/` sentinel maps to an empty list. Ranks
     * are separated by `>`, tied candidates within a rank by `=`; every token
     * is trimmed but not otherwise checked here.
     *
     * @return list<list<string>>
     */
    private static function split(string $work): array
    {
        if ($work === CefFormat::EMPTY_RANKING) {
            return [];
        }

        $rawRanking = [];

        foreach (explode('>', $work) as $rankString) {
            $rank = [];

            foreach (explode('=', $rankString) as $candidate) {
                $rank[] = trim($candidate);
            }

            $rawRanking[] = $rank;
        }

        return $rawRanking;
    }

    /**
     * @param list<list<string>> $ranks
     *
     * @throws CefFormatException
     *
     * @return list<list<string>>
     */
    private static function validate(array $ranks): array
    {
        $cleaned = [];
        $seen = [];

        foreach ($ranks as $rankIndex => $rank) {
            if (\count($rank) === 0) {
                throw new InvalidValueException(\sprintf('Rank #%d is empty.', $rankIndex + 1));
            }

            $cleanedRank = [];

            foreach ($rank as $candidate) {
                $trimmed = trim($candidate);
                CefFormat::assertValueIsClean($trimmed, 'Ranked candidate');

                if (isset($seen[$trimmed])) {
                    throw new DuplicateCandidateException(\sprintf(
                        'Candidate "%s" appears more than once in the ranking.',
                        $trimmed,
                    ));
                }

                $seen[$trimmed] = true;
                $cleanedRank[] = $trimmed;
            }

            $cleaned[] = $cleanedRank;
        }

        return $cleaned;
    }
}
