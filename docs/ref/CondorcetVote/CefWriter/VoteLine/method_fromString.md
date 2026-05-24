> CondorcetVote \ [VoteLine](class_VoteLine.md)
# Method fromString()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/VoteLine.php#L93)

```php
public static function VoteLine::fromString( string $line ): self
```

## Description
Build a {@see VoteLine} from a raw CEF vote-line string.

Accepted shape — every component except the ranking is optional:

    [tag1, tag2 || ] ranking [ ^weight] [ *quantifier] [# comment]

Both the relaxed and the compact spacing flavors are accepted, e.g.
`"A>B^7*2"` and `"A > B ^7 * 2"` parse identically. The `/EMPTY_RANKING/`
sentinel is recognised as a blank ballot.

The string is parsed into its components; the resulting `VoteLine` is
then constructed through the normal constructor, so every validation
rule (reserved characters, empty rank, duplicate candidate, positive
weight / quantifier) applies.

## Parameters

### **line:**
```php
string $line
```
**Type:** `string`



## Return
**Type:** [`CondorcetVote\CefWriter\VoteLine`](class_VoteLine.md)



## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** __
