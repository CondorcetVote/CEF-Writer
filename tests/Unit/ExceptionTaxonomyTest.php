<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\{Cef, CommentLine, VoteLine};
use CondorcetVote\CefWriter\Exception\{
    CefFormatException,
    DuplicateCandidateException,
    InvalidUtf8Exception,
    InvalidValueException,
    InvalidWriterStateException,
    ReservedCharacterException,
};
use CondorcetVote\CefWriter\Parameter\{CandidatesParameter, CustomParameter, NumberOfSeatsParameter, VotingMethodsParameter};

/*
 * Each test pins down the *specific* subclass thrown for one kind of violation,
 * and also implicitly checks that catching the base CefFormatException still
 * catches everything (since each subclass extends it).
 */

it('extends CefFormatException for every subclass', function (): void {
    expect(new InvalidUtf8Exception('x'))->toBeInstanceOf(CefFormatException::class);
    expect(new ReservedCharacterException('x'))->toBeInstanceOf(CefFormatException::class);
    expect(new InvalidValueException('x'))->toBeInstanceOf(CefFormatException::class);
    expect(new DuplicateCandidateException('x'))->toBeInstanceOf(CefFormatException::class);
    expect(new InvalidWriterStateException('x'))->toBeInstanceOf(CefFormatException::class);
});

it('throws InvalidUtf8Exception on an invalid UTF-8 byte sequence in a candidate', function (): void {
    new CandidatesParameter(["\xC3\x28"]);
})->throws(InvalidUtf8Exception::class);

it('throws InvalidUtf8Exception inside a CommentLine too', function (): void {
    new CommentLine("\xC3\x28");
})->throws(InvalidUtf8Exception::class);

it('throws ReservedCharacterException for a reserved char in a candidate', function (): void {
    new CandidatesParameter(['Alice#Bob']);
})->throws(ReservedCharacterException::class);

it('throws ReservedCharacterException for a `:` in a custom parameter name', function (): void {
    new CustomParameter('Foo:Bar', 'v');
})->throws(ReservedCharacterException::class);

it('throws ReservedCharacterException for `||` in a tag', function (): void {
    new VoteLine(ranking: [['A']], tags: ['oh||no']);
})->throws(ReservedCharacterException::class);

it('throws ReservedCharacterException for a raw vote line starting with #', function (): void {
    $buf = '';
    $cef = new Cef(string: $buf);
    $cef->addRawVoteLine('# not a vote');
})->throws(ReservedCharacterException::class);

it('throws InvalidValueException for an empty candidate name', function (): void {
    new CandidatesParameter(['']);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for a candidate with a line break', function (): void {
    new CandidatesParameter(["Ali\nce"]);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for a candidate with a null byte', function (): void {
    new CandidatesParameter(["Ali\0ce"]);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for a zero quantifier', function (): void {
    new VoteLine(ranking: [['A']], quantifier: 0);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for a zero weight', function (): void {
    new VoteLine(ranking: [['A']], weight: 0);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for a non-positive Number of Seats', function (): void {
    new NumberOfSeatsParameter(0);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for an empty Candidates list', function (): void {
    new CandidatesParameter([]);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for an empty Voting Methods list', function (): void {
    new VotingMethodsParameter([]);
})->throws(InvalidValueException::class);

it('throws InvalidValueException for an empty rank in the ranking', function (): void {
    new VoteLine([['A'], [], ['B']]);
})->throws(InvalidValueException::class);

it('throws DuplicateCandidateException when a candidate repeats in a ranking', function (): void {
    new VoteLine([['A'], ['B'], ['A']]);
})->throws(DuplicateCandidateException::class);

it('throws DuplicateCandidateException for a duplicate in the Candidates list', function (): void {
    new CandidatesParameter(['Alice', 'Bob', 'Alice']);
})->throws(DuplicateCandidateException::class);

it('throws InvalidWriterStateException when constructed with neither file nor string', function (): void {
    new Cef;
})->throws(InvalidWriterStateException::class);

it('throws InvalidWriterStateException when constructed with both file and string', function (): void {
    $buf = '';
    new Cef(file: '/tmp/x', string: $buf);
})->throws(InvalidWriterStateException::class);

it('throws InvalidWriterStateException for a parameter added after a vote', function (): void {
    $buf = '';
    $cef = new Cef(string: $buf);
    $cef->addVote(new VoteLine([['A']]));
    $cef->addParameter(new CandidatesParameter(['A']));
})->throws(InvalidWriterStateException::class);

it('throws InvalidWriterStateException for a vote-line string with no ranking', function (): void {
    VoteLine::fromString('tag1 || ');
})->throws(InvalidWriterStateException::class);

it('catching CefFormatException still catches every subclass', function (): void {
    $caught = 0;
    foreach (
        [
            fn() => new CandidatesParameter(["\xC3\x28"]),         // InvalidUtf8Exception
            fn() => new CandidatesParameter(['A#B']),              // ReservedCharacterException
            fn() => new NumberOfSeatsParameter(0),                 // InvalidValueException
            fn() => new CandidatesParameter(['A', 'A']),           // DuplicateCandidateException
            fn() => new Cef,                                       // InvalidWriterStateException
        ] as $thrower
    ) {
        try {
            $thrower();
        } catch (CefFormatException) {
            $caught++;
        }
    }
    expect($caught)->toBe(5);
});
