<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\{DuplicateCandidateException, InvalidValueException, ReservedCharacterException};
use CondorcetVote\CefWriter\{Ranking, VoteLine};

it('stores the ranks exposed on the ranks property', function (): void {
    $ranking = new Ranking([['Alice'], ['Bob', 'Charlie'], ['Eve']]);

    expect($ranking->ranks)->toBe([
        ['Alice'],
        ['Bob', 'Charlie'],
        ['Eve'],
    ]);
});

it('trims candidate names and preserves their order', function (): void {
    $ranking = new Ranking([['  Alice  ', "\tBob"], [' Charlie ']]);

    expect($ranking->ranks)->toBe([['Alice', 'Bob'], ['Charlie']]);
});

it('renders a linear ranking in both flavors', function (): void {
    $ranking = new Ranking([['A'], ['B'], ['C']]);

    expect($ranking->format(true))->toBe('A > B > C');
    expect($ranking->format(false))->toBe('A>B>C');
});

it('renders ties with the equality separator', function (): void {
    $ranking = new Ranking([['A'], ['B', 'C'], ['D']]);

    expect($ranking->format(true))->toBe('A > B = C > D');
    expect($ranking->format(false))->toBe('A>B=C>D');
});

it('defaults format() to the relaxed flavor', function (): void {
    $ranking = new Ranking([['A'], ['B']]);

    expect($ranking->format())->toBe('A > B');
});

it('casts to its relaxed string form', function (): void {
    $ranking = new Ranking([['A'], ['B', 'C']]);

    expect((string) $ranking)->toBe('A > B = C');
});

it('emits the EMPTY_RANKING sentinel for a blank ranking', function (): void {
    $ranking = new Ranking([]);

    expect($ranking->ranks)->toBe([]);
    expect($ranking->format(true))->toBe('/EMPTY_RANKING/');
    expect($ranking->format(false))->toBe('/EMPTY_RANKING/');
});

it('rejects a ranking that repeats the same candidate', function (): void {
    new Ranking([['A'], ['B'], ['A']]);
})->throws(DuplicateCandidateException::class, 'more than once');

it('rejects a duplicate that appears across separate tied groups', function (): void {
    new Ranking([['Alice', 'Bob'], ['Charlie', 'Alice']]);
})->throws(DuplicateCandidateException::class, 'more than once');

it('rejects an empty rank', function (): void {
    new Ranking([['A'], [], ['B']]);
})->throws(InvalidValueException::class, 'empty');

it('rejects a candidate containing a reserved character', function (): void {
    new Ranking([['A>B']]);
})->throws(ReservedCharacterException::class);

it('parses a ranking-only string', function (): void {
    $ranking = Ranking::fromString('Alice > Bob = Charlie');

    expect($ranking->ranks)->toBe([['Alice'], ['Bob', 'Charlie']]);
});

it('parses the EMPTY_RANKING sentinel from a string', function (): void {
    $ranking = Ranking::fromString('/EMPTY_RANKING/');

    expect($ranking->ranks)->toBe([]);
});

it('parses a compact ranking string', function (): void {
    $ranking = Ranking::fromString('A>B=C>D');

    expect($ranking->ranks)->toBe([['A'], ['B', 'C'], ['D']]);
});

it('rejects an empty ranking string', function (): void {
    Ranking::fromString('   ');
})->throws(InvalidValueException::class, 'empty');

it('rejects a tag separator inside a ranking string', function (): void {
    Ranking::fromString('evil || Alice > Bob');
})->throws(ReservedCharacterException::class, '||');

it('rejects a weight smuggled inside a ranking string', function (): void {
    Ranking::fromString('Alice > Bob ^7');
})->throws(ReservedCharacterException::class);

it('can be passed straight to a VoteLine', function (): void {
    $ranking = new Ranking([['Alice'], ['Bob', 'Charlie']]);
    $line = new VoteLine(ranking: $ranking, weight: 7);

    expect($line->ranking)->toBe([['Alice'], ['Bob', 'Charlie']]);
    expect($line->format(true))->toBe('Alice > Bob = Charlie ^7');
});
