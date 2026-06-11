<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter;

use CondorcetVote\CefWriter\Exception\{CefFormatException, InvalidValueException, InvalidWriterStateException};

/**
 * A single ballot.
 *
 * Instances are never built with `new`: use one of the static named
 * constructors — {@see fromRanking()} (typed ranks or a {@see Ranking}),
 * {@see fromString()} (a full CEF vote-line string), or
 * {@see fromRawRankingString()} (a pre-validated *verbatim* ranking string).
 *
 * The ranking is held as a {@see Ranking} value object on {@see $ranking}. An
 * empty ranking renders as the `/EMPTY_RANKING/` blank-ballot sentinel. In the
 * verbatim mode the ranking string is written untouched and is *not* parsed,
 * so {@see $ranking} is `null`.
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
    /**
     * The parsed ranking, or `null` when the ballot was built from a verbatim
     * ranking string via {@see fromRawRankingString()} — in that mode the
     * ranking is deliberately *not* parsed into a {@see Ranking} structure.
     */
    public readonly ?Ranking $ranking;

    /**
     * Verbatim, pre-validated ranking string written to the output untouched.
     * Set only in the {@see fromRawRankingString()} mode; `null` otherwise
     * (when {@see $ranking} carries the parsed structure instead).
     */
    private readonly ?string $rawRanking;

    /** @var list<string> */
    public readonly array $tags;

    public readonly ?int $weight;

    public readonly ?int $quantifier;

    public readonly ?string $inlineComment;

    /**
     * @internal Use a static named constructor instead: {@see fromRanking()},
     *           {@see fromString()} or {@see fromRawRankingString()}.
     *
     * @param Ranking|string $ranking       When `$verbatim` is `false`, the parsed
     *                                       {@see Ranking} to render. When `$verbatim`
     *                                       is `true`, a ranking-only string written
     *                                       *verbatim* (validated but not parsed).
     * @param bool           $verbatim       `true` to keep `$ranking` (a string)
     *                                       untouched, `false` to render a {@see Ranking}.
     * @param list<string>   $tags           Optional tags written before `||`.
     * @param int|null       $weight         Strictly positive weight, or `null`.
     * @param int|null       $quantifier     Strictly positive quantifier, or `null`.
     * @param string|null    $inlineComment  Single-line trailing comment, or `null`.
     *
     * @throws CefFormatException on any specification violation
     */
    public function __construct(
        Ranking|string $ranking,
        bool $verbatim,
        array $tags = [],
        ?int $weight = null,
        ?int $quantifier = null,
        ?string $inlineComment = null,
    ) {
        if ($verbatim) {
            // Verbatim mode: validate the ranking string but skip parsing it
            // into a Ranking — it is written as-is by format().
            \assert(\is_string($ranking));
            Ranking::assertValidString($ranking);
            $this->ranking = null;
            $this->rawRanking = trim($ranking);
        } else {
            \assert($ranking instanceof Ranking);
            $this->ranking = $ranking;
            $this->rawRanking = null;
        }

        $this->tags = self::validateTags($tags);

        self::assertCompanions($weight, $quantifier);

        if ($inlineComment !== null) {
            CefFormat::assertSingleLine($inlineComment, 'Inline comment');
        }

        $this->weight = $weight;
        $this->quantifier = $quantifier;
        $this->inlineComment = $inlineComment;
    }

    /**
     * Build a {@see VoteLine} from typed ranks or a ready-made {@see Ranking}.
     *
     * This is the primary, typed constructor. The `$ranking` argument is
     * either an ordered list of ranks (each inner list a non-empty group of
     * tied candidates; pass `[]` for the `/EMPTY_RANKING/` blank ballot) or a
     * {@see Ranking} value object.
     *
     * @param list<list<string>>|Ranking $ranking       Ordered ranks, or a {@see Ranking}.
     * @param list<string>               $tags           Optional tags written before `||`.
     * @param int|null                   $weight         Strictly positive weight, or `null`.
     * @param int|null                   $quantifier     Strictly positive quantifier, or `null`.
     * @param string|null                $inlineComment  Single-line trailing comment, or `null`.
     *
     * @throws CefFormatException on any specification violation
     */
    public static function fromRanking(
        array|Ranking $ranking,
        array $tags = [],
        ?int $weight = null,
        ?int $quantifier = null,
        ?string $inlineComment = null,
    ): self {
        return new self(
            ranking: $ranking instanceof Ranking ? $ranking : new Ranking($ranking),
            verbatim: false,
            tags: $tags,
            weight: $weight,
            quantifier: $quantifier,
            inlineComment: $inlineComment,
        );
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
            verbatim: false,
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
     * Build a {@see VoteLine} from a ranking-only string, kept *verbatim*.
     *
     * The special, allocation-light sibling of {@see fromRanking()}: the
     * ranking string is validated as a ranking and *nothing else* — any
     * reserved character (`^`, `*`, `#`, `;`, `,`, `/`), the `||`
     * tag separator, or a line break is rejected, so it cannot smuggle a
     * weight, quantifier, tag or inline comment — but it is **not** parsed into
     * a {@see Ranking}. The string is stored as-is and written untouched by
     * {@see format()} (only the library-built companions — the `||` separator,
     * `^weight`, `*quantifier` — follow `$autoFormat`). The resulting
     * instance therefore has a `null` {@see $ranking}. Used by
     * {@see Cef::addRawVote()}.
     *
     * @param string       $ranking    Ranking only, e.g. `"A > B = C"` or `"/EMPTY_RANKING/"`.
     * @param list<string> $tags       Optional tags written before `||`.
     * @param int|null     $weight     Strictly positive weight, or `null`.
     * @param int|null     $quantifier Strictly positive quantifier, or `null`.
     *
     * @throws CefFormatException
     */
    public static function fromRawRankingString(
        string $ranking,
        array $tags = [],
        ?int $weight = null,
        ?int $quantifier = null,
    ): self {
        return new self(
            ranking: $ranking,
            verbatim: true,
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
     *     ranking: Ranking,
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

        return [
            'ranking' => Ranking::fromString($work),
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
        return $this->assembleLine($this->renderRanking($autoFormat), $autoFormat);
    }

    /**
     * Return the ranking part of the line: the verbatim string in raw mode, or
     * the parsed ranking rendered with `$autoFormat` otherwise.
     */
    private function renderRanking(bool $autoFormat): string
    {
        if ($this->rawRanking !== null) {
            return $this->rawRanking;
        }

        \assert($this->ranking !== null);

        return $this->ranking->format($autoFormat);
    }

    /**
     * Reject a non-null, non-positive weight or quantifier.
     *
     * @throws CefFormatException
     */
    private static function assertCompanions(?int $weight, ?int $quantifier): void
    {
        if ($weight !== null && $weight < 1) {
            throw new InvalidValueException('Weight must be a positive integer.');
        }

        if ($quantifier !== null && $quantifier < 1) {
            throw new InvalidValueException('Quantifier must be a positive integer.');
        }
    }

    /**
     * Wrap a ranking string with the tag prefix and the weight / quantifier
     * suffix, using the spacing flavor selected by `$autoFormat`.
     */
    private function assembleLine(string $ranking, bool $autoFormat): string
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

            CefFormat::assertNoTagSeparator($trimmed, 'Tag');
            CefFormat::assertValueIsClean($trimmed, 'Tag');
            $cleaned[] = $trimmed;
        }

        return $cleaned;
    }
}
