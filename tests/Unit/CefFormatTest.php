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
