> CondorcetVote \ [CefWriter](../../readme.md) \ **CandidatesParameter**
# Class CandidatesParameter
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Parameter/CandidatesParameter.php#L17)

## Description
`#/Candidates:` parameter — declares the official list of candidates.

Candidate names are written separated by `;`. With auto-format on, the
separator is padded with spaces (`A ; B ; C`) for readability; otherwise
the most compact form (`A;B;C`) is used.
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [candidates(...)](property_candidates.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [getFormattedValue(...)](method_getFormattedValue.md) | __ |
| [getName(...)](method_getName.md) | __ |


## Public Representation
```php
final class CondorcetVote\CefWriter\Parameter\CandidatesParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly array $candidates;

    // Methods
    public function __construct( array $candidates );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Parameter\CandidatesParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly array $candidates;

    // Methods
    public function __construct( array $candidates );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```