> CondorcetVote \ **CommentLine**
# Class CommentLine
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/CommentLine.php#L15)

## Description
A standalone comment line (`# text`).

Inline comments attached to vote lines are not represented by this class —
they live on `VoteLine::$inlineComment` instead.
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [text(...)](property_text.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [format(...)](method_format.md) | _Render the line *without* trailing newline._ |


## Public Representation
```php
final class CondorcetVote\CefWriter\CommentLine
{

    // Properties
    public protected(set) readonly string $text;

    // Methods
    public function __construct( string $text );
    public function format( [ bool $autoFormat = true ] ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\CommentLine
{

    // Properties
    public protected(set) readonly string $text;

    // Methods
    public function __construct( string $text );
    public function format( [ bool $autoFormat = true ] ): string;

}
```