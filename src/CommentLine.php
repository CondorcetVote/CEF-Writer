<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator;

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

/**
 * A standalone comment line (`# text`).
 *
 * Inline comments attached to vote lines are not represented by this class —
 * they live on `VoteLine::$inlineComment` instead.
 */
final class CommentLine
{
    public readonly string $text;

    /**
     * @throws CefFormatException
     */
    public function __construct(string $text)
    {
        CefFormat::assertSingleLine($text, 'Comment');
        $this->text = $text;
    }

    /**
     * Render the line *without* trailing newline.
     */
    public function format(bool $autoFormat = true): string
    {
        if ($this->text === '') {
            return '#';
        }

        $needsLeadingSpace = $autoFormat && $this->text[0] !== ' ';

        return '#' . ($needsLeadingSpace ? ' ' : '') . $this->text;
    }
}
