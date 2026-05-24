> CondorcetVote \ **VoteLine**
# Class VoteLine
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/VoteLine.php#L24)

## Description
A single ballot.

The ranking is expressed as an ordered list of ranks; each rank is itself a
list of candidate names tied at that position. An empty top-level ranking
(`[]`) emits the `/EMPTY_RANKING/` blank-ballot sentinel.

Optional companions:
  - `tags`        — alphanumeric labels separated by `,`, appended before `||`;
  - `weight`      — strictly positive integer; only meaningful when the
                    `Weight Allowed` parameter is enabled in the document;
  - `quantifier`  — strictly positive integer that collapses identical votes
                    onto a single line;
  - `inlineComment` — free-form trailing comment introduced by `#`.
## Elements

### Public Static Methods
| Method Name | Description |
| ------------- | ------------- |
| [assertValidString(...)](method_assertValidString.md) | _Validate that $line is a syntactically valid CEF vote line, without allocating a VoteLine instance._ |
| [fromString(...)](method_fromString.md) | _Build a {@see VoteLine} from a raw CEF vote-line string._ |

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [inlineComment(...)](property_inlineComment.md) | __ |
| [quantifier(...)](property_quantifier.md) | __ |
| [ranking(...)](property_ranking.md) | __ |
| [tags(...)](property_tags.md) | __ |
| [weight(...)](property_weight.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [format(...)](method_format.md) | _Render the ballot — *without* trailing newline or inline comment — using the spacing flavor selected by $autoFormat._ |


## Public Representation
```php
final class CondorcetVote\CefWriter\VoteLine
{

    // Properties
    public protected(set) readonly ?string $inlineComment;
    public protected(set) readonly ?int $quantifier;
    public protected(set) readonly array $ranking;
    public protected(set) readonly array $tags;
    public protected(set) readonly ?int $weight;

    // Static Methods
    public static function assertValidString( string $line ): void;
    public static function fromString( string $line ): self;

    // Methods
    public function __construct( array $ranking, [ array $tags = [], ?int $weight = null, ?int $quantifier = null, ?string $inlineComment = null ] );
    public function format( [ bool $autoFormat = true ] ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\VoteLine
{

    // Properties
    public protected(set) readonly ?string $inlineComment;
    public protected(set) readonly ?int $quantifier;
    public protected(set) readonly array $ranking;
    public protected(set) readonly array $tags;
    public protected(set) readonly ?int $weight;

    // Static Methods
    public static function assertValidString( string $line ): void;
    public static function fromString( string $line ): self;
    private static function parseStringComponents( string $line ): array;
    private static function validateRanking( array $ranking ): array;
    private static function validateTags( array $tags ): array;

    // Methods
    public function __construct( array $ranking, [ array $tags = [], ?int $weight = null, ?int $quantifier = null, ?string $inlineComment = null ] );
    public function format( [ bool $autoFormat = true ] ): string;

}
```