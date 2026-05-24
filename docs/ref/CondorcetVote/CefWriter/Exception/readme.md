> CondorcetVote \ [CefWriter](../readme.md) \ **Exception**
# Namespace: CondorcetVote\CefWriter\Exception

## Classes

| Class Name | Description |
| ------------- | ------------- |
| [CefFormatException](CefFormatException/class_CefFormatException.md) | _Base class for every input/format violation thrown by the library._ |
| [CefWriteException](CefWriteException/class_CefWriteException.md) | _Thrown when writing to the underlying target (\SplFileObject or string buffer) fails. Distinct from {@see CefFormatException} because the cause is the I/O layer — disk full, broken pipe, closed handle..._ |
| [DuplicateCandidateException](DuplicateCandidateException/class_DuplicateCandidateException.md) | _Thrown when the same candidate label appears more than once where the CEF specification forbids it — either in #/Candidates: or in a single vote's ranking (across tied groups included)._ |
| [InvalidUtf8Exception](InvalidUtf8Exception/class_InvalidUtf8Exception.md) | _Thrown when a value contains a byte sequence that does not decode as valid UTF-8. The CEF specification mandates UTF-8, so any non-UTF-8 input is rejected before it can land in the output stream._ |
| [InvalidValueException](InvalidValueException/class_InvalidValueException.md) | _Thrown when a value is structurally impossible at the CEF level — regardless of which reserved/UTF-8 rule it would otherwise hit._ |
| [InvalidWriterStateException](InvalidWriterStateException/class_InvalidWriterStateException.md) | _Thrown when the streaming writer is asked to perform an operation that does not fit its current internal state._ |
| [ReservedCharacterException](ReservedCharacterException/class_ReservedCharacterException.md) | _Thrown when a value contains a character that the CEF format reserves for structural use and therefore forbids inside any value._ |

