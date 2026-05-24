> CondorcetVote \ [CefWriter](../../readme.md) \ **InvalidValueException**
# Class InvalidValueException
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Exception/InvalidValueException.php#L18)

## Description
Thrown when a value is structurally impossible at the CEF level —
regardless of which reserved/UTF-8 rule it would otherwise hit.

Typical triggers:
- empty string where one is required (candidate name, tag, parameter name);
- embedded line break or null byte;
- non-positive `weight` or `quantifier`;
- empty list when one or more entries are required (candidates, methods);
- empty rank inside a ranking.
## Elements


## Public Representation
```php
final class CondorcetVote\CefWriter\Exception\InvalidValueException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Exception\InvalidValueException extends CondorcetVote\CefWriter\Exception\CefFormatException implements Throwable, Stringable
{

    // Inherited Properties
    protected  InvalidValueException->code = 0;
    protected string InvalidValueException->file = '';
    protected int InvalidValueException->line = 0;
    protected  InvalidValueException->message = '';

}
```