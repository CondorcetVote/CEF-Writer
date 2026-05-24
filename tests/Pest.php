<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Cef;
use CondorcetVote\CefWriter\Exception\CefFormatException;

/**
 * Build a Cef writer that streams into a fresh string buffer, returning
 * both the writer and a closure that yields the current buffer value.
 *
 * @return array{0: Cef, 1: callable(): string}
 */
function makeStringCef(bool $autoFormat = true): array
{
    $buffer = '';
    $cef = new Cef(string: $buffer);
    $cef->autoFormat = $autoFormat;

    $reader = function () use (&$buffer): string {
        return $buffer;
    };

    return [$cef, $reader];
}

/**
 * Path to a fresh writable temp file destroyed at the end of the test run.
 */
function makeTempPath(): string
{
    $path = tempnam(sys_get_temp_dir(), 'cef-');

    if ($path === false) {
        throw new RuntimeException('Unable to create temp file.');
    }

    register_shutdown_function(static function () use ($path): void {
        if (is_file($path)) {
            @unlink($path);
        }
    });

    return $path;
}

// Re-export for clarity in tests.
class_exists(CefFormatException::class);
