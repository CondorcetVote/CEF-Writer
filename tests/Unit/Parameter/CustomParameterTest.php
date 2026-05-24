<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Exception\CefFormatException;
use CondorcetVote\CefWriter\Parameter\CustomParameter;

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

it('rejects a name containing a reserved character', function (string $reserved): void {
    new CustomParameter('Foo' . $reserved . 'Bar', 'value');
})->with(['>', '=', ';', ',', '#', '/', '*', '^'])->throws(CefFormatException::class, 'reserved');

it('rejects a name containing a colon', function (): void {
    new CustomParameter('Foo:Bar', 'value');
})->throws(CefFormatException::class, ':');

it('rejects a name containing a line break', function (string $break): void {
    new CustomParameter('Foo' . $break . 'Bar', 'value');
})->with(["\n", "\r", "\r\n"])->throws(CefFormatException::class, 'line break');

it('rejects a value containing a newline', function (): void {
    new CustomParameter('Foo', "line1\nline2");
})->throws(CefFormatException::class);

it('rejects a value containing a reserved character', function (string $reserved): void {
    new CustomParameter('Foo', 'hello' . $reserved . 'world');
})->with(['>', '=', ';', ',', '#', '/', '*', '^'])->throws(CefFormatException::class, 'reserved');

it('accepts an empty value (no required minimum content)', function (): void {
    $param = new CustomParameter('Foo', '');

    expect($param->value)->toBe('');
});

it('accepts whitespace-only value', function (): void {
    $param = new CustomParameter('Foo', '   ');

    expect($param->value)->toBe('   ');
});
