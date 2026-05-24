> CondorcetVote \ [CefWriter](../../readme.md) \ **ImplicitRankingParameter**
# Class ImplicitRankingParameter
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Parameter/ImplicitRankingParameter.php#L10)

## Description
`#/Implicit Ranking:` parameter — boolean toggle.
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [enabled(...)](property_enabled.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [getFormattedValue(...)](method_getFormattedValue.md) | __ |
| [getName(...)](method_getName.md) | __ |


## Public Representation
```php
final class CondorcetVote\CefWriter\Parameter\ImplicitRankingParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly bool $enabled;

    // Methods
    public function __construct( bool $enabled );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Parameter\ImplicitRankingParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly bool $enabled;

    // Methods
    public function __construct( bool $enabled );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```