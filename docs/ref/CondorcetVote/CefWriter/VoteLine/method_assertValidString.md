> CondorcetVote \ [VoteLine](class_VoteLine.md)
# Method assertValidString()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/VoteLine.php#L117)

```php
public static function VoteLine::assertValidString( string $line ): void
```

## Description
Validate that `$line` is a syntactically valid CEF vote line, without
allocating a `VoteLine` instance.

The exact same parsing and validation pipeline that {@see \CondorcetVote\CefWriter\fromString()}
uses is applied — only the final object construction is skipped. Useful
for hot paths that want to write a pre-built line straight to the output
after a strict format check.

## Parameters

### **line:**
```php
string $line
```
**Type:** `string`



## Return
**Type:** `void`



## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** __
