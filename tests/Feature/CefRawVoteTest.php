<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\{DuplicateCandidateException, InvalidUtf8Exception, InvalidValueException, InvalidWriterStateException, ReservedCharacterException};
use CondorcetVote\CefWriter\Parameter\CandidatesParameter;
use CondorcetVote\CefWriter\VoteLine;

it('writes a ranking-only vote line', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob > Charlie');

    expect($buffer())->toBe("Alice > Bob > Charlie\n");
});

it('writes tied candidates', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob = Charlie');

    expect($buffer())->toBe("Alice > Bob = Charlie\n");
});

it('writes the ranking verbatim regardless of autoFormat (compact in)', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addRawVote('Alice>Bob=Charlie');

    expect($buffer())->toBe("Alice>Bob=Charlie\n");
});

it('writes the ranking verbatim regardless of autoFormat (spaced in)', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: false);

    $cef->addRawVote('Alice > Bob = Charlie');

    expect($buffer())->toBe("Alice > Bob = Charlie\n");
});

it('formats the companions per autoFormat but keeps the ranking verbatim (off)', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: false);

    $cef->addRawVote('Alice > Bob', quantifier: 3, weight: 7, tags: ['a']);

    expect($buffer())->toBe("a||Alice > Bob^7*3\n");
});

it('formats the companions per autoFormat but keeps the ranking verbatim (on)', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addRawVote('Alice>Bob', quantifier: 3, weight: 7, tags: ['a']);

    expect($buffer())->toBe("a || Alice>Bob ^7 * 3\n");
});

it('appends a typed weight', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', weight: 7);

    expect($buffer())->toBe("Alice > Bob ^7\n");
});

it('appends a typed quantifier', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', quantifier: 42);

    expect($buffer())->toBe("Alice > Bob * 42\n");
});

it('appends weight and quantifier together', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', quantifier: 42, weight: 7);

    expect($buffer())->toBe("Alice > Bob ^7 * 42\n");
});

it('appends typed tags', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', tags: ['voter@example.com', 'sig:abc']);

    expect($buffer())->toBe("voter@example.com, sig:abc || Alice > Bob\n");
});

it('omits a null weight', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', weight: null);

    expect($buffer())->toBe("Alice > Bob\n");
});

it('omits a null quantifier', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', quantifier: null);

    expect($buffer())->toBe("Alice > Bob\n");
});

it('writes an explicit weight of 1 when provided', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', weight: 1);

    expect($buffer())->toBe("Alice > Bob ^1\n");
});

it('writes an explicit quantifier of 1 when provided', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', quantifier: 1);

    expect($buffer())->toBe("Alice > Bob * 1\n");
});

it('omits both companions by default', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob');

    expect($buffer())->toBe("Alice > Bob\n");
});

it('treats a null tags argument as no tags', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob', tags: null);

    expect($buffer())->toBe("Alice > Bob\n");
});

it('writes the empty-ranking sentinel', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('/EMPTY_RANKING/');

    expect($buffer())->toBe("/EMPTY_RANKING/\n");
});

it('writes the empty-ranking sentinel with a quantifier', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('/EMPTY_RANKING/', quantifier: 3);

    expect($buffer())->toBe("/EMPTY_RANKING/ * 3\n");
});

it('preserves UTF-8 candidate names', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Élise > 日本語 = 🗳');

    expect($buffer())->toBe("Élise > 日本語 = 🗳\n");
});

it('triggers the autoFormat separator just like addVote()', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addParameter(new CandidatesParameter(['Alice', 'Bob']));
    $cef->addRawVote('Alice > Bob');

    expect($buffer())->toBe("#/Candidates: Alice ; Bob\n\nAlice > Bob\n");
});

it('locks parameter writing once a raw vote has been emitted', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice');
    $cef->addParameter(new CandidatesParameter(['Alice']));
})->throws(InvalidWriterStateException::class, 'before any vote');

it('returns $this for chaining', function (): void {
    [$cef] = makeStringCef();

    $result = $cef->addRawVote('Alice');

    expect($result)->toBe($cef);
});

it('interoperates with addVote() afterwards', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addRawVote('Alice > Bob');
    $cef->addVote(VoteLine::fromRanking([['Charlie']]));

    expect($buffer())->toBe("Alice > Bob\nCharlie\n");
});

// --- Paranoid rejection: $vote must carry only a ranking -------------------

it('rejects a weight smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice > Bob ^7');
})->throws(ReservedCharacterException::class);

it('rejects a quantifier smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice > Bob * 42');
})->throws(ReservedCharacterException::class);

it('rejects an inline comment smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice > Bob # sneaky');
})->throws(ReservedCharacterException::class);

it('rejects a tag separator smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('evil || Alice > Bob');
})->throws(ReservedCharacterException::class, '||');

it('rejects a semicolon (second vote) smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice ; Bob');
})->throws(ReservedCharacterException::class);

it('rejects a comma smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice, Bob');
})->throws(ReservedCharacterException::class);

it('rejects a slash smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice/Bob');
})->throws(ReservedCharacterException::class);

it('rejects a fake EMPTY_RANKING-style sentinel built with slashes', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('/FAKE/');
})->throws(ReservedCharacterException::class);

it('rejects an embedded \\n in $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote("Alice > Bob\nCharlie");
})->throws(InvalidValueException::class);

it('rejects an embedded \\r in $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote("Alice > Bob\rCharlie");
})->throws(InvalidValueException::class);

it('rejects a CRLF injection that would smuggle a second vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote("Alice > Bob\r\nCharlie > Dave");
})->throws(InvalidValueException::class);

it('rejects a null byte smuggled inside $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote("Alice\0Bob");
})->throws(InvalidValueException::class);

it('rejects an invalid UTF-8 byte sequence in $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote("Alice \xFF\xFE Bob");
})->throws(InvalidUtf8Exception::class);

it('rejects an empty $vote string', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('');
})->throws(InvalidValueException::class, 'empty');

it('rejects a whitespace-only $vote string', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote("   \t  ");
})->throws(InvalidValueException::class, 'empty');

it('rejects a duplicate candidate in $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice > Bob > Alice');
})->throws(DuplicateCandidateException::class, 'more than once');

it('rejects an empty rank in $vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice >  > Bob');
})->throws(InvalidValueException::class);

it('rejects a zero weight argument', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice', weight: 0);
})->throws(InvalidValueException::class, 'positive');

it('rejects a negative weight argument', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice', weight: -3);
})->throws(InvalidValueException::class, 'positive');

it('rejects a zero quantifier argument', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice', quantifier: 0);
})->throws(InvalidValueException::class, 'positive');

it('rejects an empty tag in the $tags argument', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice', tags: ['ok', '']);
})->throws(InvalidValueException::class);

it('rejects a reserved character in a tag', function (): void {
    [$cef] = makeStringCef();

    $cef->addRawVote('Alice', tags: ['bad,tag']);
})->throws(ReservedCharacterException::class);
