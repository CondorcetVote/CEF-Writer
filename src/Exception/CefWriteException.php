<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Exception;

/**
 * Thrown when writing to the underlying target (`\SplFileObject` or string
 * buffer) fails. Distinct from {@see CefFormatException} because the cause is
 * the I/O layer — disk full, broken pipe, closed handle, read-only file — not
 * an invalid input from the caller.
 */
final class CefWriteException extends \RuntimeException {}
