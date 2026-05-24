> CondorcetVote \ [Cef](class_Cef.md)
# Method __construct()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Cef.php#L64)

```php
public function Cef->__construct( [ SplFileObject|SplFileInfo|string|null $file = null, ?string &$string = null ] )
```

## Parameters

### **file:**
```php
SplFileObject|SplFileInfo|string|null $file = null
```
**Type:** `SplFileObject` | `SplFileInfo` | `string` | `?null`

Open file object, info object, or filesystem path.

### **string:**
```php
?string &$string = null
```
**Type:** `?string`

String buffer to append to (passed by reference).

Exactly one of `$file` or `$string` must be provided.

## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** __
