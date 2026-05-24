# API Reference Index

## CondorcetVote\CefWriter
| Class Name | Description |
| ------------- | ------------- |
| [Cef](ref/CondorcetVote/CefWriter/Cef/class_Cef.md) | _Streaming writer for a single Condorcet Election Format document._ |
| [CommentLine](ref/CondorcetVote/CefWriter/CommentLine/class_CommentLine.md) | _A standalone comment line (# text)._ |
| [VoteLine](ref/CondorcetVote/CefWriter/VoteLine/class_VoteLine.md) | _A single ballot._ |


## CondorcetVote\CefWriter\Exception
| Class Name | Description |
| ------------- | ------------- |
| [CefFormatException](ref/CondorcetVote/CefWriter/Exception/CefFormatException/class_CefFormatException.md) | _Base class for every input/format violation thrown by the library._ |
| [CefWriteException](ref/CondorcetVote/CefWriter/Exception/CefWriteException/class_CefWriteException.md) | _Thrown when writing to the underlying target (\SplFileObject or string buffer) fails. Distinct from {@see CefFormatException} because the cause is the I/O layer — disk full, broken pipe, closed handle..._ |
| [DuplicateCandidateException](ref/CondorcetVote/CefWriter/Exception/DuplicateCandidateException/class_DuplicateCandidateException.md) | _Thrown when the same candidate label appears more than once where the CEF specification forbids it — either in #/Candidates: or in a single vote's ranking (across tied groups included)._ |
| [InvalidUtf8Exception](ref/CondorcetVote/CefWriter/Exception/InvalidUtf8Exception/class_InvalidUtf8Exception.md) | _Thrown when a value contains a byte sequence that does not decode as valid UTF-8. The CEF specification mandates UTF-8, so any non-UTF-8 input is rejected before it can land in the output stream._ |
| [InvalidValueException](ref/CondorcetVote/CefWriter/Exception/InvalidValueException/class_InvalidValueException.md) | _Thrown when a value is structurally impossible at the CEF level — regardless of which reserved/UTF-8 rule it would otherwise hit._ |
| [InvalidWriterStateException](ref/CondorcetVote/CefWriter/Exception/InvalidWriterStateException/class_InvalidWriterStateException.md) | _Thrown when the streaming writer is asked to perform an operation that does not fit its current internal state._ |
| [ReservedCharacterException](ref/CondorcetVote/CefWriter/Exception/ReservedCharacterException/class_ReservedCharacterException.md) | _Thrown when a value contains a character that the CEF format reserves for structural use and therefore forbids inside any value._ |


## CondorcetVote\CefWriter\Parameter
| Class Name | Description |
| ------------- | ------------- |
| [CandidatesParameter](ref/CondorcetVote/CefWriter/Parameter/CandidatesParameter/class_CandidatesParameter.md) | _#/Candidates: parameter — declares the official list of candidates._ |
| [CustomParameter](ref/CondorcetVote/CefWriter/Parameter/CustomParameter/class_CustomParameter.md) | _Free-form parameter for tooling that extends CEF with project-specific keys._ |
| [ImplicitRankingParameter](ref/CondorcetVote/CefWriter/Parameter/ImplicitRankingParameter/class_ImplicitRankingParameter.md) | _#/Implicit Ranking: parameter — boolean toggle._ |
| [NumberOfSeatsParameter](ref/CondorcetVote/CefWriter/Parameter/NumberOfSeatsParameter/class_NumberOfSeatsParameter.md) | _#/Number of Seats: parameter — strictly positive integer._ |
| [VotingMethodsParameter](ref/CondorcetVote/CefWriter/Parameter/VotingMethodsParameter/class_VotingMethodsParameter.md) | _#/Voting Methods: parameter — list of method identifiers separated by ;._ |
| [WeightAllowedParameter](ref/CondorcetVote/CefWriter/Parameter/WeightAllowedParameter/class_WeightAllowedParameter.md) | _#/Weight Allowed: parameter — boolean toggle._ |

| Enum Name | Description |
| ------------- | ------------- |
| [StandardParameter](ref/CondorcetVote/CefWriter/Parameter/StandardParameter/enum_StandardParameter.md) | _Enumeration of the parameter names defined by the CEF specification._ |

