> CondorcetVote \ [CefWriter](../../readme.md) \ **DuplicateCandidateException**
# Class DuplicateCandidateException
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/DuplicateCandidateException.php#L12)

## Description
Thrown when the same candidate label appears more than once where the CEF
specification forbids it — either in `#/Candidates:` or in a single vote's
ranking (across tied groups included).
## Elements


## Public Representation
```php
final class CondorcetVote\CefWriter\Exception\DuplicateCandidateException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Exception\DuplicateCandidateException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

    // Inherited Properties
    protected  DuplicateCandidateException->code = 0;
    protected string DuplicateCandidateException->file = '';
    protected int DuplicateCandidateException->line = 0;
    protected  DuplicateCandidateException->message = '';

}
```