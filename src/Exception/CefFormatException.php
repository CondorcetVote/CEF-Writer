<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Exception;

/**
 * Thrown whenever an input would produce a CEF document that violates the
 * Condorcet Election Format specification, or when the library is asked to
 * perform an operation that is forbidden by its streaming contract
 * (for example: adding a parameter after a vote has already been written).
 */
final class CefFormatException extends \RuntimeException {}
