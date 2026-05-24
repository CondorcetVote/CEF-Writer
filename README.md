# CEF Writer

A small PHP library that **streams** valid
[Condorcet Election Format](https://github.com/CondorcetVote/CondorcetElectionFormat) (CEF)
documents to a file or string with a friendly object API.

- Streaming: every `add*()` call writes one line immediately — nothing is buffered, nothing can be edited afterwards.
- Format-safe: the spec's syntactic rules (reserved characters, blank-ballot sentinel, single-line constraints, parameter-before-vote ordering) are enforced. Invalid input throws.
- Semantics-free on purpose: this library checks **format**, never **election logic** (it will, for example, happily let a vote reference a candidate that is not in `#/Candidates:`).
- Works with `\SplFileObject`, `\SplFileInfo`, a filesystem path, or a string passed **by reference**.

**[Full API reference](docs/readme.md)** — generated from the source with [PhpReference](https://github.com/julien-boudry/PhpReference).

## Requirements

- PHP **8.5** or later
- `ext-mbstring` (used for UTF-8 validation; bundled with PHP)

## Installation

```bash
composer require condorcet-vote/cef-writer
```

## Quick start

```php
use CondorcetVote\CefWriter\Cef;
use CondorcetVote\CefWriter\CommentLine;
use CondorcetVote\CefWriter\VoteLine;
use CondorcetVote\CefWriter\Parameter\CandidatesParameter;
use CondorcetVote\CefWriter\Parameter\ImplicitRankingParameter;
use CondorcetVote\CefWriter\Parameter\WeightAllowedParameter;

$cef = new Cef(file: '/tmp/election.cvotes');

$cef->addComment(new CommentLine('My beautiful election'));
$cef->addParameter(new CandidatesParameter(['Alice', 'Bob', 'Charlie']));
$cef->addParameter(new ImplicitRankingParameter(true));
$cef->addParameter(new WeightAllowedParameter(true));

$cef->addVote(new VoteLine(
    ranking: [['Alice'], ['Bob'], ['Charlie']],
    quantifier: 42,
));
$cef->addVote(new VoteLine(
    ranking: [['Charlie'], ['Alice', 'Bob']],
    weight: 7,
    quantifier: 8,
));
$cef->addVote(new VoteLine(ranking: [])); // blank ballot (/EMPTY_RANKING/)
```

produces:

```
# My beautiful election
#/Candidates: Alice ; Bob ; Charlie
#/Implicit Ranking: true
#/Weight Allowed: true

Alice > Bob > Charlie * 42
Charlie > Alice = Bob ^7 * 8
/EMPTY_RANKING/
```

## Output targets

The `Cef` constructor accepts **exactly one** of the following:

| Argument        | Type                                      | Behaviour                                       |
| --------------- | ----------------------------------------- | ----------------------------------------------- |
| `file: $path`   | `string`                                  | Opened with mode `wb` (created/truncated).      |
| `file: $info`   | `\SplFileInfo`                            | Opened with mode `wb`.                          |
| `file: $object` | `\SplFileObject` (must already be open)   | Used as-is.                                     |
| `string: $buf`  | `string` passed **by reference**          | Each line is appended to the caller's variable. |

```php
$buffer = '';
$cef = new Cef(string: $buffer);
$cef->addParameter(new CandidatesParameter(['A', 'B']));
echo $buffer; // "#/Candidates: A ; B\n"
```

## `autoFormat`

`$cef->autoFormat` is a public `bool` (default `true`):

- `true` — writes the **readable** flavor of the spec: spaces around `>`, `=`, `;`, `,`, `||`, `^`, `*`; one blank line is inserted automatically between the parameter block and the first vote.
- `false` — writes the **compact** form with no optional whitespace and no auto blank line.

```php
$cef->autoFormat = false;
$cef->addParameter(new CandidatesParameter(['A', 'B']));
$cef->addVote(new VoteLine([['A'], ['B']]));
// "#/Candidates:A;B\nA>B\n"
```

## Building blocks

### Parameters

Each standard parameter has its own typed object. Custom parameters are
supported via `CustomParameter`. The `StandardParameter` enum lists the
exact spec names.

| Class                       | CEF name             | Constructor                        |
| --------------------------- | -------------------- | ---------------------------------- |
| `CandidatesParameter`       | `Candidates`         | `array<int, string>`               |
| `NumberOfSeatsParameter`    | `Number of Seats`    | `int >= 1`                         |
| `ImplicitRankingParameter`  | `Implicit Ranking`   | `bool`                             |
| `VotingMethodsParameter`    | `Voting Methods`     | `array<int, string>`               |
| `WeightAllowedParameter`    | `Weight Allowed`     | `bool`                             |
| `CustomParameter`           | (free-form)          | `string $name, string $value`      |

Parameters can only be added before the first vote — any later call throws
`CefFormatException`.

### Vote lines

The typed way — build a `VoteLine` and pass it to `Cef::addVote()`:

```php
new VoteLine(
    ranking:       [['Alice'], ['Bob', 'Charlie']], // [] => /EMPTY_RANKING/
    tags:          ['voter@example.com'],
    weight:        7,
    quantifier:    3,
    inlineComment: 'late ballot',
);
```

Each rank is itself a list of tied candidates. An empty top-level ranking
emits the `/EMPTY_RANKING/` blank-ballot sentinel.

#### From a raw string — `VoteLine::fromString()`

Parse a full CEF vote-line string into a `VoteLine` instance. Every component
is optional except the ranking; both the relaxed (`A > B ^7 * 2`) and the
compact (`A>B^7*2`) spacing flavors are accepted, plus the `/EMPTY_RANKING/`
sentinel.

```php
$cef->addVote(VoteLine::fromString('voter@example.com || Alice > Bob ^7 * 3 # late ballot'));
```

The string is parsed, every component is validated against the same rules as
the constructor, and a `VoteLine` is returned. Throws `CefFormatException` on
any malformed component.

#### Pre-validated raw lines — `Cef::addRawVoteLine()`

When you already have ballots as text and want the fastest write path,
`addRawVoteLine()` skips the `VoteLine` allocation while still enforcing the
full CEF format:

```php
$cef->addRawVoteLine('Alice > Bob = Charlie ^7 * 8');
```

It strips one trailing line terminator (`\r\n`, `\n`, `\r`), trims, rejects
empty / multi-line / leading-`#` inputs, then runs
`VoteLine::assertValidString()` for the same deep validation as
`fromString()`. The `autoFormat` flag is **not** applied — what you pass is
what gets written. About 1.8× faster than `addVote(VoteLine::fromString())`
in practice.

#### Validation-only — `VoteLine::assertValidString()`

If you want to validate a vote-line string without allocating a `VoteLine`
(e.g. to pre-flight user input before queueing it elsewhere), call the
static `assertValidString()` — same pipeline as `fromString()`, no object
returned, throws `CefFormatException` on any violation.

### Comments and blank lines

```php
$cef->addComment(new CommentLine('section divider'));
$cef->addCommentLine('shortcut — builds the CommentLine for you');
$cef->addEmptyLine();
```

Inline comments attached to vote lines live on `VoteLine::$inlineComment`.
The CEF spec forbids inline comments on parameter lines, so the parameter
classes intentionally do not expose one.

## Errors

Two top-level hierarchies, each for a different layer.

### Format & input violations — `CefFormatException`

Base class for every specification or input violation. Catch this one to
handle *any* format-related failure uniformly; catch a specific subclass
below to branch on a kind of violation. Each message names the offending
field and the rule that was broken.

| Subclass | Thrown for |
|---|---|
| `InvalidUtf8Exception`       | Byte sequence that does not decode as valid UTF-8. |
| `ReservedCharacterException` | One of the spec-reserved characters (`> = ; , # / * ^`), a `:` in a custom parameter name, `\|\|` inside a tag, or a leading `#` on a raw vote line. |
| `InvalidValueException`      | Empty required string, embedded line break, null byte, non-positive `weight` / `quantifier`, empty `#/Candidates:` or `#/Voting Methods:` list, or empty rank inside a ranking. |
| `DuplicateCandidateException`| Same candidate label appearing twice in `#/Candidates:` or anywhere inside a ranking (including across tied groups). |
| `InvalidWriterStateException`| `Cef` constructed with neither a file nor a string target (or with both); parameter added after the first vote; vote-line string parsed without a ranking. |

All subclasses extend `CefFormatException`, which itself extends
`\RuntimeException`.

### I/O failures — `CefWriteException`

Thrown when writing to the underlying target (file or string buffer) fails —
typically a closed handle, a read-only file, or a full disk. Distinct from
`CefFormatException` because the cause is I/O, not your input. Extends
`\RuntimeException`.

## Development

```bash
composer install
vendor/bin/pest                                # run tests
vendor/bin/phpstan analyse                     # static analysis
vendor/bin/php-cs-fixer fix                    # apply lint
vendor/bin/php-cs-fixer fix --dry-run --diff   # check lint without writing
```

CI (`.github/workflows/ci.yml`) runs the test suite on Linux / Windows / macOS,
plus a dedicated job with **JIT function mode** enabled, plus PHPStan and
PHP CS Fixer.

## License

MIT — see [LICENSE](LICENSE).
