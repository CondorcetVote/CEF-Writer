<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\CefFormatException;
use CondorcetVote\CefWriter\VoteLine;

it('renders a simple linear ranking in compact form', function (): void {
    $line = new VoteLine([['A'], ['B'], ['C']]);

    expect($line->format(false))->toBe('A>B>C');
});

it('renders a simple linear ranking in pretty form', function (): void {
    $line = new VoteLine([['A'], ['B'], ['C']]);

    expect($line->format(true))->toBe('A > B > C');
});

it('renders ties with the equality separator', function (): void {
    $line = new VoteLine([['A'], ['B', 'C'], ['D']]);

    expect($line->format(false))->toBe('A>B=C>D');
    expect($line->format(true))->toBe('A > B = C > D');
});

it('stores tied candidates as a single inner array on the ranking property', function (): void {
    $line = new VoteLine([['Alice'], ['Bob', 'Charlie', 'Dave'], ['Eve']]);

    expect($line->ranking)->toBe([
        ['Alice'],
        ['Bob', 'Charlie', 'Dave'],
        ['Eve'],
    ]);
});

it('accepts a single rank with several tied candidates and no other ranks', function (): void {
    $line = new VoteLine([['Alice', 'Bob', 'Charlie']]);

    expect($line->ranking)->toBe([['Alice', 'Bob', 'Charlie']]);
    expect($line->format(true))->toBe('Alice = Bob = Charlie');
});

it('trims tied candidates individually and preserves their order', function (): void {
    $line = new VoteLine([['  Alice  ', "\tBob", ' Charlie ']]);

    expect($line->ranking)->toBe([['Alice', 'Bob', 'Charlie']]);
});

it('detects a duplicate that appears across separate tied groups', function (): void {
    new VoteLine([['Alice', 'Bob'], ['Charlie', 'Alice']]);
})->throws(CefFormatException::class, 'more than once');

it('renders weight and quantifier in the spec order', function (): void {
    $line = new VoteLine(
        ranking: [['A'], ['B']],
        weight: 7,
        quantifier: 8,
    );

    expect($line->format(false))->toBe('A>B^7*8');
    expect($line->format(true))->toBe('A > B ^7 * 8');
});

it('renders only the weight when quantifier is absent', function (): void {
    $line = new VoteLine(ranking: [['A']], weight: 3);

    expect($line->format(false))->toBe('A^3');
    expect($line->format(true))->toBe('A ^3');
});

it('renders only the quantifier when weight is absent', function (): void {
    $line = new VoteLine(ranking: [['A']], quantifier: 42);

    expect($line->format(false))->toBe('A*42');
    expect($line->format(true))->toBe('A * 42');
});

it('renders tags before the ranking separated by ||', function (): void {
    $line = new VoteLine(
        ranking: [['A'], ['B']],
        tags: ['julien@condorcet.vote', 'signature:abc'],
    );

    expect($line->format(false))->toBe('julien@condorcet.vote,signature:abc||A>B');
    expect($line->format(true))->toBe('julien@condorcet.vote, signature:abc || A > B');
});

it('emits the EMPTY_RANKING sentinel for a blank ballot', function (): void {
    $line = new VoteLine(ranking: []);

    expect($line->format(false))->toBe('/EMPTY_RANKING/');
    expect($line->format(true))->toBe('/EMPTY_RANKING/');
});

it('keeps the empty ranking when a quantifier is attached', function (): void {
    $line = new VoteLine(ranking: [], quantifier: 2);

    expect($line->format(true))->toBe('/EMPTY_RANKING/ * 2');
});

it('trims candidate names in the ranking', function (): void {
    $line = new VoteLine([['  Alice  '], ["\tBob"]]);

    expect($line->ranking)->toBe([['Alice'], ['Bob']]);
});

it('stores the inline comment without rendering it itself', function (): void {
    $line = new VoteLine([['A']], inlineComment: 'a note');

    expect($line->inlineComment)->toBe('a note');
    expect($line->format(true))->toBe('A');
});

it('rejects a ranking that repeats the same candidate', function (): void {
    new VoteLine([['A'], ['B'], ['A']]);
})->throws(CefFormatException::class, 'more than once');

it('rejects an empty rank', function (): void {
    new VoteLine([['A'], [], ['B']]);
})->throws(CefFormatException::class, 'empty');

it('rejects a candidate containing a reserved character', function (): void {
    new VoteLine([['A'], ['B>C']]);
})->throws(CefFormatException::class);

it('rejects a tag containing a reserved character', function (): void {
    new VoteLine(ranking: [['A']], tags: ['valid', 'bad;tag']);
})->throws(CefFormatException::class);

it('rejects an empty tag', function (): void {
    new VoteLine(ranking: [['A']], tags: ['valid', '   ']);
})->throws(CefFormatException::class);

it('rejects a zero weight', function (): void {
    new VoteLine(ranking: [['A']], weight: 0);
})->throws(CefFormatException::class);

it('rejects a negative weight', function (): void {
    new VoteLine(ranking: [['A']], weight: -1);
})->throws(CefFormatException::class);

it('rejects a zero quantifier', function (): void {
    new VoteLine(ranking: [['A']], quantifier: 0);
})->throws(CefFormatException::class);

it('rejects a multi-line inline comment', function (): void {
    new VoteLine(ranking: [['A']], inlineComment: "first\nsecond");
})->throws(CefFormatException::class);
