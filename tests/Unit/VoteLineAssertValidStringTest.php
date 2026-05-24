<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;
use CondorcetVote\CondorcetElectionFormatGenerator\VoteLine;

it('accepts valid vote-line strings silently', function (string $line): void {
    VoteLine::assertValidString($line);
})->with([
    'Alice > Bob',
    'Alice > Bob = Charlie',
    'Alice>Bob^7*8',
    'tag1, tag2 || Alice > Bob ^7 * 8 # note',
    '/EMPTY_RANKING/',
    '/EMPTY_RANKING/ * 2',
    'Élise > 日本語 = 🗳',
])->throwsNoExceptions();

it('rejects an empty string', function (): void {
    VoteLine::assertValidString('');
})->throws(CefFormatException::class, 'empty');

it('rejects a duplicate candidate', function (): void {
    VoteLine::assertValidString('Alice > Bob > Alice');
})->throws(CefFormatException::class, 'more than once');

it('rejects a zero weight', function (): void {
    VoteLine::assertValidString('Alice ^0');
})->throws(CefFormatException::class);

it('rejects a zero quantifier', function (): void {
    VoteLine::assertValidString('Alice * 0');
})->throws(CefFormatException::class);

it('rejects a missing ranking', function (): void {
    VoteLine::assertValidString('tag1 || ');
})->throws(CefFormatException::class, 'no ranking');

it('does not return any value', function (): void {
    $result = VoteLine::assertValidString('Alice > Bob');

    expect($result)->toBeNull();
});

it('shares its parsing pipeline with fromString — same rejection set', function (string $line): void {
    $fromStringThrew = false;
    $assertThrew = false;

    try {
        VoteLine::fromString($line);
    } catch (CefFormatException) {
        $fromStringThrew = true;
    }

    try {
        VoteLine::assertValidString($line);
    } catch (CefFormatException) {
        $assertThrew = true;
    }

    expect($assertThrew)->toBe($fromStringThrew);
})->with([
    'Alice > Bob',
    'Alice > Bob > Alice',
    'Alice ^0',
    'Alice * 0',
    'tag1, , tag2 || Alice',
    '/EMPTY_RANKING/',
    'Alice >  > Bob',
]);
