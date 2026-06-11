<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter;

use CondorcetVote\CefWriter\Exception\{CefFormatException, DuplicateCandidateException, InvalidValueException, InvalidWriterStateException, ReservedCharacterException};

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
            throw new InvalidValueException('Weight must be a positive integer.');
        }

        if ($quantifier !== null && $quantifier < 1) {
            throw new InvalidValueException('Quantifier must be a positive integer.');
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
     * Build a {@see VoteLine} from a ranking-only string plus strictly-typed
     * companions.
     *
     * Unlike {@see fromString()}, the `$ranking` argument is parsed as a
     * ranking and *nothing else*: it may contain candidate names joined by the
     * `>` (rank) and `=` (tie) operators, or the `/EMPTY_RANKING/` sentinel.
     * Any reserved character (`^`, `*`, `#`, `;`, `,`, `/`), the `||` tag
     * separator, or a line break is rejected — there is no way to smuggle a
     * weight, quantifier, tag or inline comment through the string. Those must
     * be supplied through the dedicated, typed arguments.
     *
     * @param string       $ranking    Ranking only, e.g. `"A > B = C"` or `"/EMPTY_RANKING/"`.
     * @param list<string> $tags       Optional tags written before `||`.
     * @param int|null     $weight     Strictly positive weight, or `null`.
     * @param int|null     $quantifier Strictly positive quantifier, or `null`.
     *
     * @throws CefFormatException
     */
    public static function fromRankingString(
        string $ranking,
        array $tags = [],
        ?int $weight = null,
        ?int $quantifier = null,
    ): self {
        $work = trim($ranking);

        if ($work === '') {
            throw new InvalidValueException(
                'Vote ranking string cannot be empty; use "/EMPTY_RANKING/" for a blank ballot.',
            );
        }

        // The "||" tag separator is the only forbidden pattern that
        // per-candidate validation would not catch on its own ("|" is not a
        // reserved character), so reject it explicitly here. Every reserved
        // character and line break is rejected later by validateRanking().
        if (str_contains($work, CefFormat::TAGS_SEPARATOR)) {
            throw new ReservedCharacterException(
                'Vote ranking cannot contain the "||" tag separator; pass tags through the $tags argument.',
            );
        }

        return new self(
            ranking: self::splitRanking($work),
            tags: $tags,
            weight: $weight,
            quantifier: $quantifier,
        );
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
            throw new InvalidValueException('Vote line string cannot be empty.');
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
            throw new InvalidValueException('Weight must be a positive integer.');
        }

        if ($quantifier !== null && $quantifier < 1) {
            throw new InvalidValueException('Quantifier must be a positive integer.');
        }

        if ($work === '') {
            throw new InvalidWriterStateException(\sprintf(
                'Vote line "%s" has no ranking.',
                trim($original),
            ));
        }

        $rawRanking = self::splitRanking($work);

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
        return $this->assemble($this->formatRanking($autoFormat), $autoFormat);
    }

    /**
     * Render the ballot reusing a caller-supplied, already-validated ranking
     * string *verbatim* instead of re-rendering the parsed ranking.
     *
     * Used by {@see Cef::addRawVote()} so that the raw ranking the caller
     * passed is emitted untouched (its original spacing is preserved, exactly
     * like {@see Cef::addRawVoteLine()}), while the typed companions (tags,
     * weight, quantifier) are still laid out according to `$autoFormat`.
     *
     * @internal
     */
    public function formatWithRawRanking(string $rawRanking, bool $autoFormat = true): string
    {
        return $this->assemble($rawRanking, $autoFormat);
    }

    /**
     * Wrap a ranking string with the tag prefix and the weight / quantifier
     * suffix, using the spacing flavor selected by `$autoFormat`.
     */
    private function assemble(string $ranking, bool $autoFormat): string
    {
        $line = '';

        if (\count($this->tags) > 0) {
            $tagSeparator = $autoFormat ? ', ' : ',';
            $line .= implode($tagSeparator, $this->tags);
            $line .= $autoFormat ? ' || ' : '||';
        }

        $line .= $ranking;

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
     * Split a cleaned ranking string into its raw, *un-validated* rank/tie
     * structure. The `/EMPTY_RANKING/` sentinel maps to an empty list. Ranks
     * are separated by `>`, tied candidates within a rank by `=`; every token
     * is trimmed but not otherwise checked here.
     *
     * @return list<list<string>>
     */
    private static function splitRanking(string $work): array
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
                throw new InvalidValueException('Tag cannot be empty.');
            }

            if (str_contains($trimmed, CefFormat::TAGS_SEPARATOR)) {
                throw new ReservedCharacterException('Tag cannot contain the "||" separator.');
            }

            CefFormat::assertValueIsClean($trimmed, 'Tag');
            $cleaned[] = $trimmed;
        }

        return $cleaned;
    }
}
