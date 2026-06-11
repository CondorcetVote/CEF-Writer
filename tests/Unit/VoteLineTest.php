<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\{DuplicateCandidateException, InvalidValueException, ReservedCharacterException};
use CondorcetVote\CefWriter\{Ranking, VoteLine};

it('renders a simple linear ranking in compact form', function (): void {
    $line = VoteLine::fromRanking([['A'], ['B'], ['C']]);

    expect($line->format(false))->toBe('A>B>C');
});

it('renders a simple linear ranking in pretty form', function (): void {
    $line = VoteLine::fromRanking([['A'], ['B'], ['C']]);

    expect($line->format(true))->toBe('A > B > C');
});

it('renders ties with the equality separator', function (): void {
    $line = VoteLine::fromRanking([['A'], ['B', 'C'], ['D']]);

    expect($line->format(false))->toBe('A>B=C>D');
    expect($line->format(true))->toBe('A > B = C > D');
});

it('stores tied candidates as a single inner array on the ranking property', function (): void {
    $line = VoteLine::fromRanking([['Alice'], ['Bob', 'Charlie', 'Dave'], ['Eve']]);

    expect($line->ranking->ranks)->toBe([
        ['Alice'],
        ['Bob', 'Charlie', 'Dave'],
        ['Eve'],
    ]);
});

it('accepts a single rank with several tied candidates and no other ranks', function (): void {
    $line = VoteLine::fromRanking([['Alice', 'Bob', 'Charlie']]);

    expect($line->ranking->ranks)->toBe([['Alice', 'Bob', 'Charlie']]);
    expect($line->format(true))->toBe('Alice = Bob = Charlie');
});

it('trims tied candidates individually and preserves their order', function (): void {
    $line = VoteLine::fromRanking([['  Alice  ', "\tBob", ' Charlie ']]);

    expect($line->ranking->ranks)->toBe([['Alice', 'Bob', 'Charlie']]);
});

it('detects a duplicate that appears across separate tied groups', function (): void {
    VoteLine::fromRanking([['Alice', 'Bob'], ['Charlie', 'Alice']]);
})->throws(DuplicateCandidateException::class, 'more than once');

it('renders weight and quantifier in the spec order', function (): void {
    $line = VoteLine::fromRanking(
        ranking: [['A'], ['B']],
        weight: 7,
        quantifier: 8,
    );

    expect($line->format(false))->toBe('A>B^7*8');
    expect($line->format(true))->toBe('A > B ^7 * 8');
});

it('renders only the weight when quantifier is absent', function (): void {
    $line = VoteLine::fromRanking(ranking: [['A']], weight: 3);

    expect($line->format(false))->toBe('A^3');
    expect($line->format(true))->toBe('A ^3');
});

it('renders only the quantifier when weight is absent', function (): void {
    $line = VoteLine::fromRanking(ranking: [['A']], quantifier: 42);

    expect($line->format(false))->toBe('A*42');
    expect($line->format(true))->toBe('A * 42');
});

it('renders tags before the ranking separated by ||', function (): void {
    $line = VoteLine::fromRanking(
        ranking: [['A'], ['B']],
        tags: ['julien@condorcet.vote', 'signature:abc'],
    );

    expect($line->format(false))->toBe('julien@condorcet.vote,signature:abc||A>B');
    expect($line->format(true))->toBe('julien@condorcet.vote, signature:abc || A > B');
});

it('emits the EMPTY_RANKING sentinel for a blank ballot', function (): void {
    $line = VoteLine::fromRanking(ranking: []);

    expect($line->format(false))->toBe('/EMPTY_RANKING/');
    expect($line->format(true))->toBe('/EMPTY_RANKING/');
});

it('keeps the empty ranking when a quantifier is attached', function (): void {
    $line = VoteLine::fromRanking(ranking: [], quantifier: 2);

    expect($line->format(true))->toBe('/EMPTY_RANKING/ * 2');
});

it('trims candidate names in the ranking', function (): void {
    $line = VoteLine::fromRanking([['  Alice  '], ["\tBob"]]);

    expect($line->ranking->ranks)->toBe([['Alice'], ['Bob']]);
});

it('stores the inline comment without rendering it itself', function (): void {
    $line = VoteLine::fromRanking([['A']], inlineComment: 'a note');

    expect($line->inlineComment)->toBe('a note');
    expect($line->format(true))->toBe('A');
});

it('rejects a ranking that repeats the same candidate', function (): void {
    VoteLine::fromRanking([['A'], ['B'], ['A']]);
})->throws(DuplicateCandidateException::class, 'more than once');

it('rejects an empty rank', function (): void {
    VoteLine::fromRanking([['A'], [], ['B']]);
})->throws(InvalidValueException::class, 'empty');

it('rejects a candidate containing a reserved character', function (): void {
    VoteLine::fromRanking([['A'], ['B>C']]);
})->throws(ReservedCharacterException::class);

it('rejects a tag containing a reserved character', function (): void {
    VoteLine::fromRanking(ranking: [['A']], tags: ['valid', 'bad;tag']);
})->throws(ReservedCharacterException::class);

it('rejects an empty tag', function (): void {
    VoteLine::fromRanking(ranking: [['A']], tags: ['valid', '   ']);
})->throws(InvalidValueException::class);

it('rejects a zero weight', function (): void {
    VoteLine::fromRanking(ranking: [['A']], weight: 0);
})->throws(InvalidValueException::class);

it('rejects a negative weight', function (): void {
    VoteLine::fromRanking(ranking: [['A']], weight: -1);
})->throws(InvalidValueException::class);

it('rejects a zero quantifier', function (): void {
    VoteLine::fromRanking(ranking: [['A']], quantifier: 0);
})->throws(InvalidValueException::class);

it('rejects a multi-line inline comment', function (): void {
    VoteLine::fromRanking(ranking: [['A']], inlineComment: "first\nsecond");
})->throws(InvalidValueException::class);

it('exposes the ranking as a Ranking object', function (): void {
    $line = VoteLine::fromRanking([['A'], ['B', 'C']]);

    expect($line->ranking)->toBeInstanceOf(Ranking::class);
    expect($line->ranking->ranks)->toBe([['A'], ['B', 'C']]);
});

it('reuses a Ranking instance passed to fromRanking()', function (): void {
    $ranking = new Ranking([['A'], ['B']]);
    $line = VoteLine::fromRanking(ranking: $ranking);

    expect($line->ranking)->toBe($ranking);
});

it('keeps a verbatim ranking string and leaves $ranking null', function (): void {
    $line = VoteLine::fromRawRankingString('Alice>Bob=Charlie');

    expect($line->ranking)->toBeNull();
    expect($line->format(true))->toBe('Alice>Bob=Charlie');
    expect($line->format(false))->toBe('Alice>Bob=Charlie');
});

it('formats verbatim ranking companions per autoFormat', function (): void {
    $line = VoteLine::fromRawRankingString('Alice>Bob', tags: ['a'], weight: 7, quantifier: 3);

    expect($line->format(true))->toBe('a || Alice>Bob ^7 * 3');
    expect($line->format(false))->toBe('a||Alice>Bob^7*3');
});

it('trims the verbatim ranking string', function (): void {
    $line = VoteLine::fromRawRankingString('  Alice > Bob  ');

    expect($line->format(true))->toBe('Alice > Bob');
});

it('validates the verbatim ranking string but rejects smuggled companions', function (): void {
    VoteLine::fromRawRankingString('Alice > Bob ^7');
})->throws(ReservedCharacterException::class);

it('rejects a duplicate candidate in a verbatim ranking string', function (): void {
    VoteLine::fromRawRankingString('Alice > Bob > Alice');
})->throws(DuplicateCandidateException::class, 'more than once');
