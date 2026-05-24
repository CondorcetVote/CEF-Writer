<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator;

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;
use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\ParameterInterface;

/**
 * Streaming writer for a single Condorcet Election Format document.
 *
 * Each `add*()` call emits one line to the underlying target *immediately* —
 * the library never buffers more than a single line in memory and previously
 * written content cannot be edited.
 *
 * The target is chosen at construction time:
 *   - an open `\SplFileObject` (passed through);
 *   - an `\SplFileInfo` (opened with mode `wb`);
 *   - a filesystem path (opened with mode `wb`);
 *   - a string passed **by reference** that the writer will append to.
 *
 * # Phases
 *
 * Parameters must be emitted before votes. Comments and empty lines may be
 * emitted at any time. Once the first `VoteLine` is written, calling
 * `addParameter()` throws a {@see CefFormatException}.
 *
 * # autoFormat
 *
 * When `true` (default), the writer follows the visually relaxed flavor of
 * the spec — spaces around `>`, `=`, `;`, `,`; one blank line automatically
 * inserted between the parameter block and the first vote. When `false`, the
 * most compact form is emitted.
 */
final class Cef
{
    public bool $autoFormat = true;

    /**
     * The active file target, or `null` when writing to a string.
     */
    public readonly ?\SplFileObject $file;

    /**
     * Reference to the caller's string buffer in string mode, `null` in file mode.
     */
    private ?string $stringTarget;

    private bool $parameterEmitted = false;

    private bool $voteEmitted = false;

    private bool $autoSeparatorWritten = false;

    /**
     * @param \SplFileObject|\SplFileInfo|string|null $file   Open file object, info object, or filesystem path.
     * @param string|null                             $string String buffer to append to (passed by reference).
     *
     * Exactly one of `$file` or `$string` must be provided.
     *
     * @throws CefFormatException
     */
    public function __construct(
        \SplFileObject|\SplFileInfo|string|null $file = null,
        ?string &$string = null,
    ) {
        $hasFile = $file !== null;
        $hasString = $string !== null;

        if ($hasFile === $hasString) {
            throw new CefFormatException(
                'Exactly one of $file or $string must be provided to the Cef constructor.',
            );
        }

        if ($hasString) {
            $this->file = null;
            $this->stringTarget = &$string;

            return;
        }

        $this->stringTarget = null;

        if ($file instanceof \SplFileObject) {
            $this->file = $file;
        } elseif ($file instanceof \SplFileInfo) {
            $this->file = $file->openFile('wb');
        } else {
            \assert(\is_string($file));
            $this->file = new \SplFileObject($file, 'wb');
        }
    }

    /**
     * Emit a parameter line `#/Name: value`.
     *
     * @throws CefFormatException if a vote has already been written
     */
    public function addParameter(ParameterInterface $parameter): self
    {
        if ($this->voteEmitted) {
            throw new CefFormatException(
                'Parameters must be written before any vote line.',
            );
        }

        $separator = $this->autoFormat ? ': ' : ':';
        $line = '#/' . $parameter->getName() . $separator . $parameter->getFormattedValue($this->autoFormat);

        $this->writeLine($line);
        $this->parameterEmitted = true;

        return $this;
    }

    /**
     * Emit a vote line. Locks parameter mode permanently.
     */
    public function addVote(VoteLine $vote): self
    {
        $this->writeAutoSeparatorIfNeeded();

        $line = $vote->format($this->autoFormat);

        if ($vote->inlineComment !== null) {
            $line .= $this->renderInlineComment($vote->inlineComment);
        }

        $this->writeLine($line);
        $this->voteEmitted = true;

        return $this;
    }

    /**
     * Emit a vote line directly from a pre-built string, skipping the
     * allocation of a {@see VoteLine} instance. Use this when you already have
     * ballots as text and want the fastest path to the output.
     *
     * The full CEF vote-line format is enforced — the same validation rules
     * that {@see VoteLine::fromString()} applies are run via
     * {@see VoteLine::assertValidString()}. In particular:
     *   - structural checks first: a single trailing line terminator
     *     (`\r\n`, `\n`, `\r`) is stripped, surrounding whitespace is trimmed,
     *     the result must be non-empty, must not contain any remaining
     *     `\r`/`\n`, and must not start with `#` (which would be a comment or
     *     a parameter line, not a vote);
     *   - format checks then: tags, ranking, weight, quantifier and inline
     *     comment are parsed and validated against every CEF rule (reserved
     *     characters, empty rank, duplicate candidate, positive weight /
     *     quantifier, single-line comment).
     *
     * The `autoFormat` flag has no effect on a raw line: what you pass is
     * what gets written (after structural cleaning).
     *
     * @throws CefFormatException
     */
    public function addRawVoteLine(string $line): self
    {
        $cleaned = preg_replace('/\r\n\z|[\r\n]\z/', '', $line) ?? $line;
        $cleaned = trim($cleaned);

        if ($cleaned === '') {
            throw new CefFormatException('Raw vote line cannot be empty.');
        }

        if (preg_match('/[\r\n]/', $cleaned) === 1) {
            throw new CefFormatException(
                'Raw vote line must be a single line; embedded newlines are not allowed.',
            );
        }

        if ($cleaned[0] === '#') {
            throw new CefFormatException(
                'Raw vote line cannot start with "#"; that would be a comment or parameter line, not a vote.',
            );
        }

        VoteLine::assertValidString($cleaned);

        $this->writeAutoSeparatorIfNeeded();
        $this->writeLine($cleaned);
        $this->voteEmitted = true;

        return $this;
    }

    /**
     * Emit a standalone comment line.
     */
    public function addComment(CommentLine $comment): self
    {
        $this->writeLine($comment->format($this->autoFormat));

        return $this;
    }

    /**
     * Convenience helper: build a {@see CommentLine} from raw text and emit it
     * in a single call.
     */
    public function addCommentLine(string $text): self
    {
        return $this->addComment(new CommentLine($text));
    }

    /**
     * Emit an empty line.
     */
    public function addEmptyLine(): self
    {
        $this->writeLine('');

        return $this;
    }

    /**
     * Insert one blank line between the parameter block and the first vote
     * when `autoFormat` is on. Idempotent.
     */
    private function writeAutoSeparatorIfNeeded(): void
    {
        if (
            $this->autoFormat
            && $this->parameterEmitted
            && ! $this->voteEmitted
            && ! $this->autoSeparatorWritten
        ) {
            $this->writeLine('');
            $this->autoSeparatorWritten = true;
        }
    }

    private function renderInlineComment(string $comment): string
    {
        if (! $this->autoFormat) {
            return '#' . $comment;
        }

        $needsLeadingSpace = $comment === '' || $comment[0] !== ' ';

        return ' #' . ($needsLeadingSpace ? ' ' : '') . $comment;
    }

    private function writeLine(string $content): void
    {
        $line = $content . "\n";

        if ($this->file !== null) {
            $this->file->fwrite($line);

            return;
        }

        if ($this->stringTarget === null) {
            throw new \LogicException(
                'Cef writer has no target: neither $file nor $stringTarget is set. '
                . 'This indicates a corrupted internal state that the constructor should have prevented.',
            );
        }

        $this->stringTarget .= $line;
    }
}
