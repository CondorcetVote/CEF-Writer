<?php

declare(strict_types=1);

namespace CondorcetVote\CefWriter\Parameter;

/**
 * Contract every parameter object (standard or custom) must implement.
 *
 * A parameter knows how to render its own *name* and *value* but not how to
 * assemble the final line: line assembly is the responsibility of the writer,
 * which decides spacing based on the `autoFormat` flag.
 */
interface ParameterInterface
{
    /**
     * The parameter name, as it must appear after the `#/` prefix.
     */
    public function getName(): string;

    /**
     * The parameter value, already serialized but **without** the surrounding
     * `#/Name: ` prefix. The boolean `$autoFormat` flag lets the parameter
     * choose whether to expand its value with optional whitespace.
     */
    public function getFormattedValue(bool $autoFormat = true): string;
}
