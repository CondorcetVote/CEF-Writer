> CondorcetVote \ [CefWriter](../../readme.md) \ **InvalidWriterStateException**
# Class InvalidWriterStateException
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/InvalidWriterStateException.php#L17)

## Description
Thrown when the streaming writer is asked to perform an operation that
does not fit its current internal state.

Typical triggers:
- adding a parameter after the first vote has been emitted;
- constructing a `Cef` with neither a file nor a string target, or with
  both at once;
- parsing a vote-line string that ends up without a ranking.
## Elements


## Public Representation
```php
final class CondorcetVote\CefWriter\Exception\InvalidWriterStateException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Exception\InvalidWriterStateException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

    // Inherited Properties
    protected  InvalidWriterStateException->code = 0;
    protected string InvalidWriterStateException->file = '';
    protected int InvalidWriterStateException->line = 0;
    protected  InvalidWriterStateException->message = '';

}
```