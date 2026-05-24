<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\CefFormat;
use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;

it('lists the reserved characters mandated by the spec', function (): void {
    expect(CefFormat::RESERVED_CHARACTERS)
        ->toBe(['>', '=', ';', ',', '#', '/', '*', '^']);
});

it('exposes the blank-ballot sentinel verbatim', function (): void {
    expect(CefFormat::EMPTY_RANKING)->toBe('/EMPTY_RANKING/');
});

it('exposes the tags separator', function (): void {
    expect(CefFormat::TAGS_SEPARATOR)->toBe('||');
});

it('rejects empty strings', function (): void {
    CefFormat::assertValueIsClean('', 'X');
})->throws(CefFormatException::class, 'empty');

it('rejects values containing reserved characters', function (string $reserved): void {
    CefFormat::assertValueIsClean('foo' . $reserved . 'bar', 'X');
})->with(['>', '=', ';', ',', '#', '/', '*', '^'])->throws(CefFormatException::class, 'reserved');

it('rejects values containing line breaks', function (string $break): void {
    CefFormat::assertValueIsClean('foo' . $break . 'bar', 'X');
})->with(["\n", "\r", "\r\n"])->throws(CefFormatException::class, 'line break');

it('accepts inline-comment text that has no line break', function (): void {
    CefFormat::assertSingleLine('this is fine; with # symbols / and *', 'X');
})->throwsNoExceptions();

it('rejects inline-comment text that has a line break', function (): void {
    CefFormat::assertSingleLine("ok\nno good", 'X');
})->throws(CefFormatException::class);

it('rejects values containing a null byte (assertValueIsClean)', function (): void {
    CefFormat::assertValueIsClean("Ali\0ce", 'X');
})->throws(CefFormatException::class, 'null byte');

it('rejects values containing a null byte (assertNoReservedNorLineBreak)', function (): void {
    CefFormat::assertNoReservedNorLineBreak("Ali\0ce", 'X');
})->throws(CefFormatException::class, 'null byte');

it('rejects values containing a null byte (assertSingleLine)', function (): void {
    CefFormat::assertSingleLine("Ali\0ce", 'X');
})->throws(CefFormatException::class, 'null byte');

it('rejects invalid UTF-8 sequences (assertValueIsClean)', function (string $badUtf8): void {
    CefFormat::assertValueIsClean($badUtf8, 'X');
})->with([
    'lone continuation byte' => "\x80",
    'truncated 2-byte'       => "\xC3",
    'invalid 2-byte pair'    => "\xC3\x28",
    'overlong encoding'      => "\xC0\xAF",
    'invalid 4-byte'         => "\xF0\x28\x8C\x28",
])->throws(CefFormatException::class, 'invalid UTF-8');

it('rejects invalid UTF-8 sequences (assertNoReservedNorLineBreak)', function (): void {
    CefFormat::assertNoReservedNorLineBreak("\xC3\x28", 'X');
})->throws(CefFormatException::class, 'invalid UTF-8');

it('rejects invalid UTF-8 sequences (assertSingleLine)', function (): void {
    CefFormat::assertSingleLine("\xC3\x28", 'X');
})->throws(CefFormatException::class, 'invalid UTF-8');

it('still accepts valid UTF-8 multi-byte sequences', function (): void {
    CefFormat::assertValueIsClean('Élise 日本語 🗳', 'X');
})->throwsNoExceptions();
