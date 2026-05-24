> CondorcetVote \ [CefWriter](../../readme.md) \ **InvalidUtf8Exception**
# Class InvalidUtf8Exception
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/InvalidUtf8Exception.php#L12)

## Description
Thrown when a value contains a byte sequence that does not decode as valid
UTF-8. The CEF specification mandates UTF-8, so any non-UTF-8 input is
rejected before it can land in the output stream.
## Elements


## Public Representation
```php
final class CondorcetVote\CefWriter\Exception\InvalidUtf8Exception extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Exception\InvalidUtf8Exception extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

    // Inherited Properties
    protected  InvalidUtf8Exception->code = 0;
    protected string InvalidUtf8Exception->file = '';
    protected int InvalidUtf8Exception->line = 0;
    protected  InvalidUtf8Exception->message = '';

}
```