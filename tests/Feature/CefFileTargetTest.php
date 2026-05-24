<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\{Cef, VoteLine};
use CondorcetVote\CefWriter\Parameter\CandidatesParameter;

it('writes to a path provided as a string', function (): void {
    $path = makeTempPath();

    $cef = new Cef(file: $path);
    $cef->autoFormat = false;
    $cef->addParameter(new CandidatesParameter(['A', 'B']));
    $cef->addVote(new VoteLine([['A'], ['B']]));

    $cef->file?->fflush();
    unset($cef);

    expect(file_get_contents($path))->toBe("#/Candidates:A;B\nA>B\n");
});

it('writes to a provided SplFileInfo (path is opened in write mode)', function (): void {
    $path = makeTempPath();
    $info = new SplFileInfo($path);

    $cef = new Cef(file: $info);
    $cef->autoFormat = false;
    $cef->addParameter(new CandidatesParameter(['A']));

    $cef->file?->fflush();
    unset($cef);

    expect(file_get_contents($path))->toBe("#/Candidates:A\n");
});

it('writes to an already-open SplFileObject', function (): void {
    $path = makeTempPath();
    $file = new SplFileObject($path, 'wb');

    $cef = new Cef(file: $file);
    $cef->autoFormat = false;
    $cef->addParameter(new CandidatesParameter(['Alice', 'Bob']));
    $cef->addVote(new VoteLine([['Alice']]));

    $file->fflush();
    unset($cef, $file);

    expect(file_get_contents($path))->toBe("#/Candidates:Alice;Bob\nAlice\n");
});

it('exposes the file handle through the readonly $file property', function (): void {
    $path = makeTempPath();

    $cef = new Cef(file: $path);

    expect($cef->file)->toBeInstanceOf(SplFileObject::class);
});

it('exposes $file as null when writing to a string buffer', function (): void {
    $buffer = '';

    $cef = new Cef(string: $buffer);

    expect($cef->file)->toBeNull();
});
