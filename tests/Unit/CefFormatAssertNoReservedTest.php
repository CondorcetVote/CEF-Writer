<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\CefFormat;
use CondorcetVote\CefWriter\Exception\{InvalidValueException, ReservedCharacterException};

it('accepts an empty string (unlike assertValueIsClean)', function (): void {
    CefFormat::assertNoReservedNorLineBreak('', 'X');
})->throwsNoExceptions();

it('rejects reserved characters', function (string $reserved): void {
    CefFormat::assertNoReservedNorLineBreak('foo' . $reserved . 'bar', 'X');
})->with(['>', '=', ';', ',', '#', '/', '*', '^'])->throws(ReservedCharacterException::class, 'reserved');

it('rejects line breaks', function (string $break): void {
    CefFormat::assertNoReservedNorLineBreak('foo' . $break . 'bar', 'X');
})->with(["\n", "\r", "\r\n"])->throws(InvalidValueException::class, 'line break');

it('accepts UTF-8 content without reserved characters', function (): void {
    CefFormat::assertNoReservedNorLineBreak('Élise 日本語 🗳', 'X');
})->throwsNoExceptions();
