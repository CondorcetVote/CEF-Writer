<?php

declare(strict_types=1);

use CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException;
use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\CustomParameter;

it('exposes the supplied name and value', function (): void {
    $param = new CustomParameter('My Setting', 'some-value');

    expect($param->getName())->toBe('My Setting');
    expect($param->getFormattedValue(false))->toBe('some-value');
    expect($param->getFormattedValue(true))->toBe('some-value');
});

it('trims the name', function (): void {
    $param = new CustomParameter('   My Setting   ', 'value');

    expect($param->name)->toBe('My Setting');
});

it('preserves whitespace inside the value', function (): void {
    $param = new CustomParameter('Key', '  spaced value  ');

    expect($param->value)->toBe('  spaced value  ');
});

it('rejects an empty name', function (): void {
    new CustomParameter('   ', 'value');
})->throws(CefFormatException::class, 'empty');

it('rejects a name containing a reserved character', function (): void {
    new CustomParameter('Foo#Bar', 'value');
})->throws(CefFormatException::class);

it('rejects a name containing a colon', function (): void {
    new CustomParameter('Foo:Bar', 'value');
})->throws(CefFormatException::class);

it('rejects a value containing a newline', function (): void {
    new CustomParameter('Foo', "line1\nline2");
})->throws(CefFormatException::class);
