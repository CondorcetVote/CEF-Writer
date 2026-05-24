> CondorcetVote \ [Cef](class_Cef.md)
# Method addRawVoteLine()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Cef.php#L160)

```php
public function Cef->addRawVoteLine( string $line ): self
```

## Description
Emit a vote line directly from a pre-built string, skipping the
allocation of a {@see VoteLine} instance. Use this when you already have
ballots as text and want the fastest path to the output.

The full CEF vote-line format is enforced — the same validation rules
that {@see \CondorcetVote\CefWriter\VoteLine::fromString()} applies are run via
{@see \CondorcetVote\CefWriter\VoteLine::assertValidString()}. In particular:
  - structural checks first: a single trailing line terminator
    (`\r\n`, `\n`, `\r`) is stripped, surrounding whitespace is trimmed,
    the result must be non-empty, must not contain any remaining
    `\r`/`\n`, and must not start with `#` (which would be a comment or
    a parameter line, not a vote);
  - format checks then: tags, ranking, weight, quantifier and inline
    comment are parsed and validated against every CEF rule (reserved
    characters, empty rank, duplicate candidate, positive weight /
    quantifier, single-line comment).

The `autoFormat` flag has no effect on a raw line: what you pass is
what gets written (after structural cleaning).

## Parameters

### **line:**
```php
string $line
```
**Type:** `string`



## Return
**Type:** [`CondorcetVote\CefWriter\Cef`](class_Cef.md)



## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** __
