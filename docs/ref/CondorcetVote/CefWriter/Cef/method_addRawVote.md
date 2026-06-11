> CondorcetVote \ [Cef](class_Cef.md)
# Method addRawVote()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Cef.php#L221)

```php
public function Cef->addRawVote( string $vote, [ ?int $quantifier = null, ?int $weight = null, ?array $tags = null ] ): self
```

## Description
Emit a vote line from a **ranking-only** string plus strictly-typed
companions — the secure, paranoid sibling of {@see addRawVoteLine()}.

Whereas {@see \CondorcetVote\CefWriter\addRawVoteLine()} accepts a full vote line (and therefore
lets the caller embed tags, a weight, a quantifier or an inline comment
inside the text), `addRawVote()` guarantees that `$vote` carries *only*
a ranking. Any line break, the `||` tag separator, and every reserved
character (`^`, `*`, `#`, `;`, `,`, `/`) are rejected, so the string can
never smuggle a weight, quantifier, tag, inline comment or second vote
into the output. Use this when the ranking comes from an untrusted
source.

Weight, quantifier and tags are supplied exclusively through the typed
parameters. `$weight` and `$quantifier` are nullable and default to
`null`, in which case they are omitted from the output (keeping the line
as short as possible); when provided they must be strictly positive.

Just like {@see \CondorcetVote\CefWriter\addRawVoteLine()}, the ranking string itself is written
**verbatim** — its original spacing is preserved and `autoFormat` does
not reformat it. The `autoFormat` flag still governs the layout of the
library-built companions (the `||` tag separator, `^weight`,
`*quantifier`).

## Parameters

### **vote:**
```php
string $vote
```
**Type:** `string`

Ranking only, e.g. `"A > B = C"` or `"/EMPTY_RANKING/"`.

### **quantifier:**
```php
?int $quantifier = null
```
**Type:** `?int`

Strictly positive quantifier, or `null` to omit.

### **weight:**
```php
?int $weight = null
```
**Type:** `?int`

Strictly positive weight, or `null` to omit.

### **tags:**
```php
?array $tags = null
```
**Type:** `?array`

Optional tags written before `||`.

## Return
**Type:** [`CondorcetVote\CefWriter\Cef`](class_Cef.md)



## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** __
