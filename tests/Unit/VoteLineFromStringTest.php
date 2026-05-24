<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;
use CondorcetVote\CondorcetElectionFormatGenerator\VoteLine;

it('parses a simple ranking', function (): void {
    $line = VoteLine::fromString('A > B > C');

    expect($line->ranking)->toBe([['A'], ['B'], ['C']]);
    expect($line->tags)->toBe([]);
    expect($line->weight)->toBeNull();
    expect($line->quantifier)->toBeNull();
    expect($line->inlineComment)->toBeNull();
});

it('parses a compact ranking (no spaces)', function (): void {
    $line = VoteLine::fromString('A>B>C');

    expect($line->ranking)->toBe([['A'], ['B'], ['C']]);
});

it('parses ties with the equality separator', function (): void {
    $line = VoteLine::fromString('A > B = C > D');

    expect($line->ranking)->toBe([['A'], ['B', 'C'], ['D']]);
});

it('parses several candidates tied at a single rank', function (): void {
    $line = VoteLine::fromString('Alice = Bob = Charlie');

    expect($line->ranking)->toBe([['Alice', 'Bob', 'Charlie']]);
});

it('parses ties in compact form (no spaces around =)', function (): void {
    $line = VoteLine::fromString('A>B=C=D>E');

    expect($line->ranking)->toBe([['A'], ['B', 'C', 'D'], ['E']]);
});

it('preserves the order of tied candidates inside a rank', function (): void {
    $line = VoteLine::fromString('Zulu = Alpha = Mike');

    expect($line->ranking)->toBe([['Zulu', 'Alpha', 'Mike']]);
});

it('parses ties together with weight, quantifier and tags', function (): void {
    $line = VoteLine::fromString('tag1, tag2 || Alice = Bob > Charlie = Dave ^7 * 8');

    expect($line->tags)->toBe(['tag1', 'tag2']);
    expect($line->ranking)->toBe([['Alice', 'Bob'], ['Charlie', 'Dave']]);
    expect($line->weight)->toBe(7);
    expect($line->quantifier)->toBe(8);
});

it('rejects a duplicate that appears across separate tied groups', function (): void {
    VoteLine::fromString('Alice = Bob > Charlie = Alice');
})->throws(CefFormatException::class, 'more than once');

it('rejects a duplicate within the same tied group', function (): void {
    VoteLine::fromString('Alice = Alice > Bob');
})->throws(CefFormatException::class, 'more than once');

it('parses candidate names that contain spaces', function (): void {
    $line = VoteLine::fromString('Candidate A > Candidate B = Candidate C');

    expect($line->ranking)->toBe([['Candidate A'], ['Candidate B', 'Candidate C']]);
});

it('parses UTF-8 candidate names', function (): void {
    $line = VoteLine::fromString('Élise > 日本語 > Müller');

    expect($line->ranking)->toBe([['Élise'], ['日本語'], ['Müller']]);
});

it('parses a trailing quantifier', function (): void {
    $line = VoteLine::fromString('A > B * 42');

    expect($line->ranking)->toBe([['A'], ['B']]);
    expect($line->quantifier)->toBe(42);
    expect($line->weight)->toBeNull();
});

it('parses a trailing quantifier in compact form', function (): void {
    $line = VoteLine::fromString('A>B*42');

    expect($line->ranking)->toBe([['A'], ['B']]);
    expect($line->quantifier)->toBe(42);
});

it('parses a trailing weight', function (): void {
    $line = VoteLine::fromString('A > B ^7');

    expect($line->ranking)->toBe([['A'], ['B']]);
    expect($line->weight)->toBe(7);
    expect($line->quantifier)->toBeNull();
});

it('parses weight then quantifier in the spec order', function (): void {
    $line = VoteLine::fromString('A > B ^7 * 8');

    expect($line->weight)->toBe(7);
    expect($line->quantifier)->toBe(8);
});

it('parses weight + quantifier in compact form', function (): void {
    $line = VoteLine::fromString('A>B^7*8');

    expect($line->weight)->toBe(7);
    expect($line->quantifier)->toBe(8);
});

it('parses tags before the ranking', function (): void {
    $line = VoteLine::fromString('julien@example.com, signature:abc || A > B');

    expect($line->tags)->toBe(['julien@example.com', 'signature:abc']);
    expect($line->ranking)->toBe([['A'], ['B']]);
});

it('parses tags + ranking + weight + quantifier together', function (): void {
    $line = VoteLine::fromString('tag1, tag2 || A > B ^3 * 5');

    expect($line->tags)->toBe(['tag1', 'tag2']);
    expect($line->ranking)->toBe([['A'], ['B']]);
    expect($line->weight)->toBe(3);
    expect($line->quantifier)->toBe(5);
});

it('parses an inline comment', function (): void {
    $line = VoteLine::fromString('A > B # my note');

    expect($line->ranking)->toBe([['A'], ['B']]);
    expect($line->inlineComment)->toBe('my note');
});

it('keeps the inline comment after weight and quantifier', function (): void {
    $line = VoteLine::fromString('A > B ^7 * 2 # late ballot');

    expect($line->weight)->toBe(7);
    expect($line->quantifier)->toBe(2);
    expect($line->inlineComment)->toBe('late ballot');
});

it('parses the EMPTY_RANKING sentinel', function (): void {
    $line = VoteLine::fromString('/EMPTY_RANKING/');

    expect($line->ranking)->toBe([]);
});

it('parses an EMPTY_RANKING with a quantifier', function (): void {
    $line = VoteLine::fromString('/EMPTY_RANKING/ * 2');

    expect($line->ranking)->toBe([]);
    expect($line->quantifier)->toBe(2);
});

it('parses a single-candidate vote', function (): void {
    $line = VoteLine::fromString('Alice');

    expect($line->ranking)->toBe([['Alice']]);
});

it('round-trips the spec example "Candidate C > Candidate A = Candidate B ^7 * 8"', function (): void {
    $line = VoteLine::fromString('Candidate C > Candidate A = Candidate B ^7 * 8');

    expect($line->format(true))->toBe('Candidate C > Candidate A = Candidate B ^7 * 8');
});

it('rejects an empty string', function (): void {
    VoteLine::fromString('');
})->throws(CefFormatException::class, 'empty');

it('rejects a string with only whitespace', function (): void {
    VoteLine::fromString('   ');
})->throws(CefFormatException::class);

it('rejects a string with only an inline comment (no ranking)', function (): void {
    VoteLine::fromString('# just a comment');
})->throws(CefFormatException::class, 'no ranking');

it('rejects a string with only tags and no ranking', function (): void {
    VoteLine::fromString('tag1 || ');
})->throws(CefFormatException::class, 'no ranking');

it('rejects a candidate that contains a reserved character', function (): void {
    VoteLine::fromString('A > B/C');
})->throws(CefFormatException::class);

it('rejects a duplicate candidate', function (): void {
    VoteLine::fromString('A > B > A');
})->throws(CefFormatException::class, 'more than once');

it('rejects an empty rank in the middle of the ranking', function (): void {
    VoteLine::fromString('A >  > B');
})->throws(CefFormatException::class);

it('rejects a zero quantifier', function (): void {
    VoteLine::fromString('A * 0');
})->throws(CefFormatException::class);

it('rejects an empty tag in the tags list', function (): void {
    VoteLine::fromString('tag1, , tag2 || A');
})->throws(CefFormatException::class);
