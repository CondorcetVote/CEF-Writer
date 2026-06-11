> CondorcetVote \ [VoteLine](class_VoteLine.md)
# Method fromRankingString()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/VoteLine.php#L141)

```php
public static function VoteLine::fromRankingString( string $ranking, [ array $tags = [], ?int $weight = null, ?int $quantifier = null ] ): self
```

## Description
Build a {@see VoteLine} from a ranking-only string plus strictly-typed
companions.

Unlike {@see \CondorcetVote\CefWriter\fromString()}, the `$ranking` argument is parsed as a
ranking and *nothing else*: it may contain candidate names joined by the
`>` (rank) and `=` (tie) operators, or the `/EMPTY_RANKING/` sentinel.
Any reserved character (`^`, `*`, `#`, `;`, `,`, `/`), the `||` tag
separator, or a line break is rejected — there is no way to smuggle a
weight, quantifier, tag or inline comment through the string. Those must
be supplied through the dedicated, typed arguments.

## Parameters

### **ranking:**
```php
string $ranking
```
**Type:** `string`

Ranking only, e.g. `"A > B = C"` or `"/EMPTY_RANKING/"`.

### **tags:**
```php
array $tags = []
```
**Type:** `array`

Optional tags written before `||`.

### **weight:**
```php
?int $weight = null
```
**Type:** `?int`

Strictly positive weight, or `null`.

### **quantifier:**
```php
?int $quantifier = null
```
**Type:** `?int`

Strictly positive quantifier, or `null`.

## Return
**Type:** [`CondorcetVote\CefWriter\VoteLine`](class_VoteLine.md)



## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** __
