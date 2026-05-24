<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator;

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

/**
 * A single ballot.
 *
 * The ranking is expressed as an ordered list of ranks; each rank is itself a
 * list of candidate names tied at that position. An empty top-level ranking
 * (`[]`) emits the `/EMPTY_RANKING/` blank-ballot sentinel.
 *
 * Optional companions:
 *   - `tags`        — alphanumeric labels separated by `,`, appended before `||`;
 *   - `weight`      — strictly positive integer; only meaningful when the
 *                     `Weight Allowed` parameter is enabled in the document;
 *   - `quantifier`  — strictly positive integer that collapses identical votes
 *                     onto a single line;
 *   - `inlineComment` — free-form trailing comment introduced by `#`.
 */
final class VoteLine
{
    /** @var list<list<string>> */
    public readonly array $ranking;

    /** @var list<string> */
    public readonly array $tags;

    public readonly ?int $weight;

    public readonly ?int $quantifier;

    public readonly ?string $inlineComment;

    /**
     * @param list<list<string>> $ranking      Ordered ranks; each inner list is non-empty.
     *                                         Pass `[]` for the `/EMPTY_RANKING/` blank ballot.
     * @param list<string>       $tags         Optional tags written before `||`.
     * @param int|null           $weight       Strictly positive weight, or `null`.
     * @param int|null           $quantifier   Strictly positive quantifier, or `null`.
     * @param string|null        $inlineComment Single-line trailing comment, or `null`.
     *
     * @throws CefFormatException on any specification violation
     */
    public function __construct(
        array $ranking,
        array $tags = [],
        ?int $weight = null,
        ?int $quantifier = null,
        ?string $inlineComment = null,
    ) {
        $this->ranking = self::validateRanking($ranking);
        $this->tags = self::validateTags($tags);

        if ($weight !== null && $weight < 1) {
            throw new CefFormatException('Weight must be a positive integer.');
        }

        if ($quantifier !== null && $quantifier < 1) {
            throw new CefFormatException('Quantifier must be a positive integer.');
        }

        if ($inlineComment !== null) {
            CefFormat::assertSingleLine($inlineComment, 'Inline comment');
        }

        $this->weight = $weight;
        $this->quantifier = $quantifier;
        $this->inlineComment = $inlineComment;
    }

    /**
     * Render the ballot — *without* trailing newline or inline comment — using
     * the spacing flavor selected by `$autoFormat`.
     */
    public function format(bool $autoFormat = true): string
    {
        $line = '';

        if (\count($this->tags) > 0) {
            $tagSeparator = $autoFormat ? ', ' : ',';
            $line .= implode($tagSeparator, $this->tags);
            $line .= $autoFormat ? ' || ' : '||';
        }

        $line .= $this->formatRanking($autoFormat);

        if ($this->weight !== null) {
            $line .= $autoFormat ? ' ^' . $this->weight : '^' . $this->weight;
        }

        if ($this->quantifier !== null) {
            $line .= $autoFormat ? ' * ' . $this->quantifier : '*' . $this->quantifier;
        }

        return $line;
    }

    private function formatRanking(bool $autoFormat = true): string
    {
        if (\count($this->ranking) === 0) {
            return CefFormat::EMPTY_RANKING;
        }

        $rankSep = $autoFormat ? ' > ' : '>';
        $tieSep = $autoFormat ? ' = ' : '=';

        $ranks = array_map(
            static fn(array $rank): string => implode($tieSep, $rank),
            $this->ranking,
        );

        return implode($rankSep, $ranks);
    }

    /**
     * @param list<list<string>> $ranking
     *
     * @throws CefFormatException
     *
     * @return list<list<string>>
     */
    private static function validateRanking(array $ranking): array
    {
        $cleaned = [];
        $seen = [];

        foreach ($ranking as $rankIndex => $rank) {
            if (\count($rank) === 0) {
                throw new CefFormatException(\sprintf('Rank #%d is empty.', $rankIndex + 1));
            }

            $cleanedRank = [];

            foreach ($rank as $candidate) {
                $trimmed = trim($candidate);
                CefFormat::assertValueIsClean($trimmed, 'Ranked candidate');

                if (isset($seen[$trimmed])) {
                    throw new CefFormatException(\sprintf(
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

    /**
     * @param list<string> $tags
     *
     * @throws CefFormatException
     *
     * @return list<string>
     */
    private static function validateTags(array $tags): array
    {
        $cleaned = [];

        foreach ($tags as $tag) {
            $trimmed = trim($tag);

            if ($trimmed === '') {
                throw new CefFormatException('Tag cannot be empty.');
            }

            if (str_contains($trimmed, CefFormat::TAGS_SEPARATOR)) {
                throw new CefFormatException('Tag cannot contain the "||" separator.');
            }

            CefFormat::assertValueIsClean($trimmed, 'Tag');
            $cleaned[] = $trimmed;
        }

        return $cleaned;
    }
}
