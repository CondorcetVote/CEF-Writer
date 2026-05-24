# Condorcet Election Format Generator

A small, PHP library that **streams** valid
[Condorcet Election Format](https://github.com/CondorcetVote/CondorcetElectionFormat) (CEF)
documents to a file or string with a friendly object API.

- Streaming: every `add*()` call writes one line immediately — nothing is buffered, nothing can be edited afterwards.
- Format-safe: the spec's syntactic rules (reserved characters, blank-ballot sentinel, single-line constraints, parameter-before-vote ordering) are enforced. Invalid input throws.
- Semantics-free on purpose: this library checks **format**, never **election logic** (it will, for example, happily let a vote reference a candidate that is not in `#/Candidates:`).
- Works with `\SplFileObject`, `\SplFileInfo`, a filesystem path, or a string passed **by reference**.

## Installation

```bash
composer require julien/condorcet-election-format-generator
```

## Quick start

```php
use CondorcetVote\CondorcetElectionFormatGenerator\Cef;
use CondorcetVote\CondorcetElectionFormatGenerator\CommentLine;
use CondorcetVote\CondorcetElectionFormatGenerator\VoteLine;
use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\CandidatesParameter;
use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\ImplicitRankingParameter;
use CondorcetVote\CondorcetElectionFormatGenerator\Parameter\WeightAllowedParameter;

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

### Comments and blank lines

```php
$cef->addComment(new CommentLine('section divider'));
$cef->addEmptyLine();
```

Inline comments attached to vote lines live on `VoteLine::$inlineComment`.
The CEF spec forbids inline comments on parameter lines, so the parameter
classes intentionally do not expose one.

## Errors

Any specification violation throws
`CondorcetVote\CondorcetElectionFormatGenerator\Exception\CefFormatException`.
The exception message names the offending field and the rule that was
broken — for example:

- empty candidate list,
- duplicate candidate in `#/Candidates:` or within a single ranking,
- a reserved character (`> = ; , # / * ^`) inside any structured value,
- a line break in a name, tag, or inline comment,
- a non-positive `weight` / `quantifier`,
- adding a parameter after a vote has been emitted.

## Development

```bash
composer install
vendor/bin/pest         # run tests
vendor/bin/phpstan      # static analysis (level 8)
vendor/bin/php-cs-fixer fix
```

## License

MIT — see `composer.json`.
