> CondorcetVote \ **Cef**
# Class Cef
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Cef.php#L36)

## Description
Streaming writer for a single Condorcet Election Format document.

Each `add*()` call emits one line to the underlying target *immediately* —
the library never buffers more than a single line in memory and previously
written content cannot be edited.

The target is chosen at construction time:
  - an open `\SplFileObject` (passed through);
  - an `\SplFileInfo` (opened with mode `wb`);
  - a filesystem path (opened with mode `wb`);
  - a string passed **by reference** that the writer will append to.

# Phases

Parameters must be emitted before votes. Comments and empty lines may be
emitted at any time. Once the first `VoteLine` is written, calling
`addParameter()` throws a {@see \CondorcetVote\CefWriter\Exception\CefFormatException}.

# autoFormat

When `true` (default), the writer follows the visually relaxed flavor of
the spec — spaces around `>`, `=`, `;`, `,`; one blank line automatically
inserted between the parameter block and the first vote. When `false`, the
most compact form is emitted.
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [autoFormat(...)](property_autoFormat.md) | __ |
| [file(...)](property_file.md) | _The active file target, or null when writing to a string._ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [addComment(...)](method_addComment.md) | _Emit a standalone comment line._ |
| [addCommentLine(...)](method_addCommentLine.md) | _Convenience helper: build a {@see CommentLine} from raw text and emit it in a single call._ |
| [addEmptyLine(...)](method_addEmptyLine.md) | _Emit an empty line._ |
| [addParameter(...)](method_addParameter.md) | _Emit a parameter line #/Name: value._ |
| [addRawVoteLine(...)](method_addRawVoteLine.md) | _Emit a vote line directly from a pre-built string, skipping the allocation of a {@see VoteLine} instance. Use this when you already have ballots as text and want the fastest path to the output._ |
| [addVote(...)](method_addVote.md) | _Emit a vote line. Locks parameter mode permanently._ |


## Public Representation
```php
final class CondorcetVote\CefWriter\Cef
{

    // Properties
    public bool $autoFormat = true;
    public protected(set) readonly ?SplFileObject $file;

    // Methods
    public function __construct( [ SplFileObject|SplFileInfo|string|null $file = null, ?string &$string = null ] );
    public function addComment( CondorcetVote\CefWriter\CommentLine $comment ): self;
    public function addCommentLine( string $text ): self;
    public function addEmptyLine( ): self;
    public function addParameter( CondorcetVote\CefWriter\Parameter\ParameterInterface $parameter ): self;
    public function addRawVoteLine( string $line ): self;
    public function addVote( CondorcetVote\CefWriter\VoteLine $vote ): self;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Cef
{

    // Properties
    public bool $autoFormat = true;
    public protected(set) readonly ?SplFileObject $file;
    private bool $autoSeparatorWritten = false;
    private bool $parameterEmitted = false;
    private ?string $stringTarget;
    private bool $voteEmitted = false;

    // Methods
    public function __construct( [ SplFileObject|SplFileInfo|string|null $file = null, ?string &$string = null ] );
    public function addComment( CondorcetVote\CefWriter\CommentLine $comment ): self;
    public function addCommentLine( string $text ): self;
    public function addEmptyLine( ): self;
    public function addParameter( CondorcetVote\CefWriter\Parameter\ParameterInterface $parameter ): self;
    public function addRawVoteLine( string $line ): self;
    public function addVote( CondorcetVote\CefWriter\VoteLine $vote ): self;

}
```