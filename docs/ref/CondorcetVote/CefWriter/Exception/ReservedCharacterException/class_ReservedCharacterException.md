> CondorcetVote \ [CefWriter](../../readme.md) \ **ReservedCharacterException**
# Class ReservedCharacterException
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/ReservedCharacterException.php#L15)

## Description
Thrown when a value contains a character that the CEF format reserves for
structural use and therefore forbids inside any value.

Covers the eight spec-listed reserved characters (`> = ; , # / * ^`) as
well as the secondary syntactic separators the library enforces: `:` in a
custom parameter name, `||` in a tag, and a leading `#` on a raw vote line.
## Elements


## Public Representation
```php
final class CondorcetVote\CefWriter\Exception\ReservedCharacterException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Exception\ReservedCharacterException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

    // Inherited Properties
    protected  ReservedCharacterException->code = 0;
    protected string ReservedCharacterException->file = '';
    protected int ReservedCharacterException->line = 0;
    protected  ReservedCharacterException->message = '';

}
```