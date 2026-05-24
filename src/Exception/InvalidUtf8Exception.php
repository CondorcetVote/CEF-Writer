<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Exception;

/**
 * Thrown when a value contains a byte sequence that does not decode as valid
 * UTF-8. The CEF specification mandates UTF-8, so any non-UTF-8 input is
 * rejected before it can land in the output stream.
 */
final class InvalidUtf8Exception extends CefFormatException {}
