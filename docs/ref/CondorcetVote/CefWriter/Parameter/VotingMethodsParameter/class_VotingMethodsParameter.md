> CondorcetVote \ [CefWriter](../../readme.md) \ **VotingMethodsParameter**
# Class VotingMethodsParameter
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Parameter/VotingMethodsParameter.php#L13)

## Description
`#/Voting Methods:` parameter — list of method identifiers separated by `;`.
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [methods(...)](property_methods.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [getFormattedValue(...)](method_getFormattedValue.md) | __ |
| [getName(...)](method_getName.md) | __ |


## Public Representation
```php
final class CondorcetVote\CefWriter\Parameter\VotingMethodsParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly array $methods;

    // Methods
    public function __construct( array $methods );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Parameter\VotingMethodsParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly array $methods;

    // Methods
    public function __construct( array $methods );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```