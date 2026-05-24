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
     * Build a {@see VoteLine} from a raw CEF vote-line string.
     *
     * Accepted shape — every component except the ranking is optional:
     *
     *     [tag1, tag2 || ] ranking [ ^weight] [ *quantifier] [# comment]
     *
     * Both the relaxed and the compact spacing flavors are accepted, e.g.
     * `"A>B^7*2"` and `"A > B ^7 * 2"` parse identically. The `/EMPTY_RANKING/`
     * sentinel is recognised as a blank ballot.
     *
     * The string is parsed into its components; the resulting `VoteLine` is
     * then constructed through the normal constructor, so every validation
     * rule (reserved characters, empty rank, duplicate candidate, positive
     * weight / quantifier) applies.
     *
     * @throws CefFormatException
     */
    public static function fromString(string $line): self
    {
        $parts = self::parseStringComponents($line);

        return new self(
            ranking: $parts['ranking'],
            tags: $parts['tags'],
            weight: $parts['weight'],
            quantifier: $parts['quantifier'],
            inlineComment: $parts['inlineComment'],
        );
    }

    /**
     * Validate that `$line` is a syntactically valid CEF vote line, without
     * allocating a `VoteLine` instance.
     *
     * The exact same parsing and validation pipeline that {@see fromString()}
     * uses is applied — only the final object construction is skipped. Useful
     * for hot paths that want to write a pre-built line straight to the output
     * after a strict format check.
     *
     * @throws CefFormatException
     */
    public static function assertValidString(string $line): void
    {
        self::parseStringComponents($line);
    }

    /**
     * Shared parser+validator used by {@see fromString()} and
     * {@see assertValidString()}.
     *
     * Trims the input, extracts every component, and runs the same per-field
     * validation (reserved characters, empty rank, duplicate candidate,
     * positive weight / quantifier, single-line comment) that the constructor
     * performs. Returns the components as a named array.
     *
     *
     * @throws CefFormatException
     *
     * @return array{
     *     ranking: list<list<string>>,
     *     tags: list<string>,
     *     weight: ?int,
     *     quantifier: ?int,
     *     inlineComment: ?string,
     * }
     */
    private static function parseStringComponents(string $line): array
    {
        $original = $line;
        $work = trim($line);

        if ($work === '') {
            throw new CefFormatException('Vote line string cannot be empty.');
        }

        $inlineComment = null;
        $hashPos = strpos($work, '#');

        if ($hashPos !== false) {
            $after = substr($work, $hashPos + 1);

            if (str_starts_with($after, ' ')) {
                $after = substr($after, 1);
            }

            $after = rtrim($after);
            $inlineComment = $after !== '' ? $after : null;
            $work = rtrim(substr($work, 0, $hashPos));
        }

        if ($inlineComment !== null) {
            CefFormat::assertSingleLine($inlineComment, 'Inline comment');
        }

        $rawTags = [];
        $separator = CefFormat::TAGS_SEPARATOR;
        $separatorPos = strpos($work, $separator);

        if ($separatorPos !== false) {
            $tagsPart = substr($work, 0, $separatorPos);
            $work = trim(substr($work, $separatorPos + \strlen($separator)));

            foreach (explode(',', $tagsPart) as $rawTag) {
                $rawTags[] = trim($rawTag);
            }
        }

        $weight = null;
        $quantifier = null;

        if (preg_match('/^(.*?)(?:\s*\^\s*(\d+))?(?:\s*\*\s*(\d+))?\s*$/s', $work, $matches, \PREG_UNMATCHED_AS_NULL) === 1) {
            $work = trim($matches[1]);

            if (isset($matches[2])) {
                $weight = (int) $matches[2];
            }

            if (isset($matches[3])) {
                $quantifier = (int) $matches[3];
            }
        }

        if ($weight !== null && $weight < 1) {
            throw new CefFormatException('Weight must be a positive integer.');
        }

        if ($quantifier !== null && $quantifier < 1) {
            throw new CefFormatException('Quantifier must be a positive integer.');
        }

        if ($work === '') {
            throw new CefFormatException(\sprintf(
                'Vote line "%s" has no ranking.',
                trim($original),
            ));
        }

        if ($work === CefFormat::EMPTY_RANKING) {
            $rawRanking = [];
        } else {
            $rawRanking = [];

            foreach (explode('>', $work) as $rankString) {
                $rank = [];

                foreach (explode('=', $rankString) as $candidate) {
                    $rank[] = trim($candidate);
                }

                $rawRanking[] = $rank;
            }
        }

        return [
            'ranking' => self::validateRanking($rawRanking),
            'tags' => self::validateTags($rawTags),
            'weight' => $weight,
            'quantifier' => $quantifier,
            'inlineComment' => $inlineComment,
        ];
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
