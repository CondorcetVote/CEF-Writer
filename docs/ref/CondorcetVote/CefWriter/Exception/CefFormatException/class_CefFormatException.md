> CondorcetVote \ [CefWriter](../../readme.md) \ **CefFormatException**
# Class CefFormatException
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/CefFormatException.php#L19)

## Description
Base class for every input/format violation thrown by the library.

Catch this class to handle any format-related failure uniformly; catch one
of the dedicated subclasses ({@see \CondorcetVote\CefWriter\Exception\InvalidUtf8Exception},
{@see \CondorcetVote\CefWriter\Exception\ReservedCharacterException}, {@see \CondorcetVote\CefWriter\Exception\InvalidValueException},
{@see \CondorcetVote\CefWriter\Exception\DuplicateCandidateException}, {@see \CondorcetVote\CefWriter\Exception\InvalidWriterStateException}) to
branch on a specific kind of violation.

Non-final on purpose so the library and downstream callers can refine the
hierarchy further if needed.
## Elements


## Public Representation
```php
class CondorcetVote\CefWriter\Exception\CefFormatException extends RuntimeException implements Stringable, Throwable
{

}
```

## Full Representation
```php
class CondorcetVote\CefWriter\Exception\CefFormatException extends RuntimeException implements Stringable, Throwable
{

    // Inherited Properties
    protected  CefFormatException->code = 0;
    protected string CefFormatException->file = '';
    protected int CefFormatException->line = 0;
    protected  CefFormatException->message = '';

}
```