> CondorcetVote \ [Cef](class_Cef.md)
# Method addParameter()
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Cef.php#L101)

```php
public function Cef->addParameter( CondorcetVote\CefWriter\Parameter\ParameterInterface $parameter ): self
```

## Description
Emit a parameter line `#/Name: value`.

## Parameters

### **parameter:**
```php
CondorcetVote\CefWriter\Parameter\ParameterInterface $parameter
```
**Type:** `CondorcetVote\CefWriter\Parameter\ParameterInterface`



## Return
**Type:** [`CondorcetVote\CefWriter\Cef`](class_Cef.md)



## Throws
- **[\CondorcetVote\CefWriter\Exception\CefFormatException](../Exception/CefFormatException/class_CefFormatException.md)** _if a vote has already been written_
