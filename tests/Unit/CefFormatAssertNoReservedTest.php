<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\CefFormat;
use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

it('accepts an empty string (unlike assertValueIsClean)', function (): void {
    CefFormat::assertNoReservedNorLineBreak('', 'X');
})->throwsNoExceptions();

it('rejects reserved characters', function (string $reserved): void {
    CefFormat::assertNoReservedNorLineBreak('foo' . $reserved . 'bar', 'X');
})->with(['>', '=', ';', ',', '#', '/', '*', '^'])->throws(CefFormatException::class, 'reserved');

it('rejects line breaks', function (string $break): void {
    CefFormat::assertNoReservedNorLineBreak('foo' . $break . 'bar', 'X');
})->with(["\n", "\r", "\r\n"])->throws(CefFormatException::class, 'line break');

it('accepts UTF-8 content without reserved characters', function (): void {
    CefFormat::assertNoReservedNorLineBreak('Élise 日本語 🗳', 'X');
})->throwsNoExceptions();
