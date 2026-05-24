<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;
use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\CandidatesParameter;

it('exposes the standard CEF parameter name', function (): void {
    expect(new CandidatesParameter(['A'])->getName())->toBe('Candidates');
});

it('emits compact format when autoFormat is off', function (): void {
    $param = new CandidatesParameter(['Alice', 'Bob', 'Charlie']);

    expect($param->getFormattedValue(false))->toBe('Alice;Bob;Charlie');
});

it('emits pretty format when autoFormat is on', function (): void {
    $param = new CandidatesParameter(['Alice', 'Bob', 'Charlie']);

    expect($param->getFormattedValue(true))->toBe('Alice ; Bob ; Charlie');
});

it('trims candidate names', function (): void {
    $param = new CandidatesParameter(['  Alice  ', "\tBob\t"]);

    expect($param->candidates)->toBe(['Alice', 'Bob']);
});

it('preserves UTF-8 candidate names', function (): void {
    $param = new CandidatesParameter(['Élise', '日本語', 'Müller']);

    expect($param->getFormattedValue(false))->toBe('Élise;日本語;Müller');
});

it('rejects an empty list', function (): void {
    new CandidatesParameter([]);
})->throws(CefFormatException::class, 'empty');

it('rejects an empty candidate name', function (): void {
    new CandidatesParameter(['Alice', '   ']);
})->throws(CefFormatException::class);

it('rejects duplicate candidates', function (): void {
    new CandidatesParameter(['Alice', 'Bob', 'Alice']);
})->throws(CefFormatException::class, 'Duplicate');

it('rejects a candidate containing a reserved character', function (string $reserved): void {
    new CandidatesParameter(['Alice', 'Bob' . $reserved . 'X']);
})->with(['>', '=', ';', ',', '#', '/', '*', '^'])->throws(CefFormatException::class, 'reserved');

it('rejects a candidate containing a newline', function (): void {
    new CandidatesParameter(["Alice\nMalice"]);
})->throws(CefFormatException::class);
