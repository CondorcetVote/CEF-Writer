<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Exception;

/**
 * Base class for every input/format violation thrown by the library.
 *
 * Catch this class to handle any format-related failure uniformly; catch one
 * of the dedicated subclasses ({@see InvalidUtf8Exception},
 * {@see ReservedCharacterException}, {@see InvalidValueException},
 * {@see DuplicateCandidateException}, {@see InvalidWriterStateException}) to
 * branch on a specific kind of violation.
 *
 * Non-final on purpose so the library and downstream callers can refine the
 * hierarchy further if needed.
 */
class CefFormatException extends \RuntimeException {}
