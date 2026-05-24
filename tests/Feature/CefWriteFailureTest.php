<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\Cef;
use CondorcetVote\CefWriter\Exception\CefWriteException;
use CondorcetVote\CefWriter\Parameter\CandidatesParameter;

/**
 * Stream wrapper whose every write reports failure. Used to simulate a broken
 * underlying file target without touching the filesystem.
 */
final class FailingStreamWrapper
{
    /** @var resource|null */
    public $context;

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        return true;
    }

    public function stream_write(string $data): int|false
    {
        return false;
    }

    public function stream_close(): void {}

    public function stream_eof(): bool
    {
        return true;
    }

    public function stream_stat(): array|false
    {
        return ['size' => 0, 'mode' => 0o100_644];
    }

    public function stream_flush(): bool
    {
        return true;
    }

    /** @return array<int|string, int>|false */
    public function url_stat(string $path, int $flags): array|false
    {
        return ['size' => 0, 'mode' => 0o100_644, 'mtime' => 0, 'ctime' => 0, 'atime' => 0];
    }
}

beforeAll(function (): void {
    if (! \in_array('cef-fail', stream_get_wrappers(), true)) {
        stream_wrapper_register('cef-fail', FailingStreamWrapper::class);
    }
});

it('throws CefWriteException when the underlying file write fails', function (): void {
    $file = new SplFileObject('cef-fail://target', 'wb');
    $cef = new Cef(file: $file);

    $cef->addParameter(new CandidatesParameter(['Alice', 'Bob']));
})->throws(CefWriteException::class, 'Failed to write');

it('reports the byte count that failed to write', function (): void {
    $file = new SplFileObject('cef-fail://target', 'wb');
    $cef = new Cef(file: $file);

    try {
        $cef->addParameter(new CandidatesParameter(['Alice']));
        $this->fail('expected CefWriteException');
    } catch (CefWriteException $e) {
        expect($e->getMessage())->toContain('bytes');
        expect($e->getMessage())->toContain('fwrite returned');
    }
});
