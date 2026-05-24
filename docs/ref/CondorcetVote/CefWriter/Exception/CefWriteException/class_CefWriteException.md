> CondorcetVote \ [CefWriter](../../readme.md) \ **CefWriteException**
# Class CefWriteException
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/CefWriteException.php#L13)

## Description
Thrown when writing to the underlying target (`\SplFileObject` or string
buffer) fails. Distinct from {@see CefFormatException} because the cause is
the I/O layer — disk full, broken pipe, closed handle, read-only file — not
an invalid input from the caller.
## Elements


## Public Representation
```php
final class CondorcetVote\CefWriter\Exception\CefWriteException extends RuntimeException implements Stringable, Throwable
{

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Exception\CefWriteException extends RuntimeException implements Stringable, Throwable
{

    // Inherited Properties
    protected  CefWriteException->code = 0;
    protected string CefWriteException->file = '';
    protected int CefWriteException->line = 0;
    protected  CefWriteException->message = '';

}
```