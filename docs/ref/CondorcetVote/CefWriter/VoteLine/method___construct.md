> CondorcetVote \ [VoteLine](class_VoteLine.md)
# Method __construct()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/VoteLine.php#L48)

```php
public function VoteLine->__construct( array $ranking, [ array $tags = [], ?int $weight = null, ?int $quantifier = null, ?string $inlineComment = null ] )
```

## Parameters

### **ranking:**
```php
array $ranking
```
**Type:** `array`

Ordered ranks; each inner list is non-empty.
Pass `[]` for the `/EMPTY_RANKING/` blank ballot.

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

### **inlineComment:**
```php
?string $inlineComment = null
```
**Type:** `?string`

Single-line trailing comment, or `null`.

## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** _on any specification violation_
