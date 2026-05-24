<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\CefFormatException;
use CondorcetVote\CefWriter\Parameter\CandidatesParameter;
use CondorcetVote\CefWriter\VoteLine;

it('writes a raw vote line verbatim', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine('Alice > Bob = Charlie ^7 * 8');

    expect($buffer())->toBe("Alice > Bob = Charlie ^7 * 8\n");
});

it('does not reformat the raw line based on autoFormat', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addRawVoteLine('Alice>Bob^7*8');

    expect($buffer())->toBe("Alice>Bob^7*8\n");
});

it('strips a single trailing \\n provided in the input', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine("Alice > Bob\n");

    expect($buffer())->toBe("Alice > Bob\n");
});

it('strips a trailing \\r\\n provided in the input', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine("Alice > Bob\r\n");

    expect($buffer())->toBe("Alice > Bob\n");
});

it('strips a trailing lone \\r provided in the input', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine("Alice > Bob\r");

    expect($buffer())->toBe("Alice > Bob\n");
});

it('trims leading and trailing whitespace from the input', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine("   Alice > Bob   \n");

    expect($buffer())->toBe("Alice > Bob\n");
});

it('triggers the autoFormat separator just like addVote()', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addParameter(new CandidatesParameter(['Alice', 'Bob']));
    $cef->addRawVoteLine('Alice > Bob');

    expect($buffer())->toBe("#/Candidates: Alice ; Bob\n\nAlice > Bob\n");
});

it('locks parameter writing once a raw vote line has been emitted', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('Alice');
    $cef->addParameter(new CandidatesParameter(['Alice']));
})->throws(CefFormatException::class, 'before any vote');

it('rejects an empty string', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('');
})->throws(CefFormatException::class, 'empty');

it('rejects a whitespace-only string', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine("   \t  \n");
})->throws(CefFormatException::class, 'empty');

it('rejects a multi-line string (embedded \\n)', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine("Alice > Bob\nCharlie > Dave");
})->throws(CefFormatException::class, 'single line');

it('rejects a multi-line string (embedded \\r)', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine("Alice > Bob\rCharlie > Dave");
})->throws(CefFormatException::class, 'single line');

it('rejects a line that starts with # (would be a comment line)', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('# a comment, not a vote');
})->throws(CefFormatException::class);

it('rejects a line that starts with #/ (would be a parameter line)', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('#/Candidates: A;B');
})->throws(CefFormatException::class);

it('still accepts a line that ENDS with an inline comment (#) — only leading # is forbidden', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine('Alice > Bob # a note');

    expect($buffer())->toBe("Alice > Bob # a note\n");
});

it('preserves UTF-8 content', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine('Élise > 日本語 = 🗳');

    expect($buffer())->toBe("Élise > 日本語 = 🗳\n");
});

it('returns $this for chaining', function (): void {
    [$cef] = makeStringCef();

    $result = $cef->addRawVoteLine('Alice');

    expect($result)->toBe($cef);
});

it('still allows addVote() with a typed VoteLine after a raw vote line', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine('Alice > Bob');
    $cef->addVote(new VoteLine([['Charlie']]));

    expect($buffer())->toBe("Alice > Bob\nCharlie\n");
});

it('accepts every form a CEF vote line can take', function (string $line): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVoteLine($line);

    expect($buffer())->toBe($line . "\n");
})->with([
    'simple ranking'                => 'Alice > Bob > Charlie',
    'compact ranking'               => 'Alice>Bob>Charlie',
    'tied candidates'               => 'Alice > Bob = Charlie',
    'tags + ranking'                => 'voter@example.com, sig:abc || Alice > Bob',
    'weight'                        => 'Alice > Bob ^7',
    'quantifier'                    => 'Alice > Bob * 42',
    'weight + quantifier'           => 'Alice > Bob ^7 * 42',
    'compact weight + quantifier'   => 'Alice>Bob^7*42',
    'inline comment'                => 'Alice > Bob # late ballot',
    'empty ranking sentinel'        => '/EMPTY_RANKING/',
    'empty ranking with quantifier' => '/EMPTY_RANKING/ * 3',
    'utf-8 candidates'              => 'Élise > 日本語 = 🗳',
]);

it('rejects a duplicate candidate inside the ranking', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('Alice > Bob > Alice');
})->throws(CefFormatException::class, 'more than once');

it('rejects a duplicate candidate across tied groups', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('Alice = Bob > Charlie = Alice');
})->throws(CefFormatException::class, 'more than once');

it('rejects an empty rank in the middle of the ranking', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('Alice >  > Bob');
})->throws(CefFormatException::class);

it('rejects a zero quantifier', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('Alice * 0');
})->throws(CefFormatException::class);

it('rejects a zero weight', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('Alice ^0');
})->throws(CefFormatException::class);

it('rejects an empty tag in the tags list', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('tag1, , tag2 || Alice');
})->throws(CefFormatException::class);

it('rejects a line that ends with a stray pipe (would-be empty ranking after tags)', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('tag1 || ');
})->throws(CefFormatException::class, 'no ranking');

it('rejects a line that is only an inline comment after tags', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVoteLine('tag1 || # only a comment');
})->throws(CefFormatException::class, 'no ranking');
