> CondorcetVote \ [CefWriter](../../readme.md) \ **CustomParameter**
# Class CustomParameter
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Parameter/CustomParameter.php#L18)

## Description
Free-form parameter for tooling that extends CEF with project-specific keys.

The name must avoid every reserved character and `:`, since `:` separates
a parameter from its value. The value must avoid line breaks but is
otherwise free-form (the spec only reserves characters for *structured*
values).
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [name(...)](property_name.md) | __ |
| [value(...)](property_value.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [getFormattedValue(...)](method_getFormattedValue.md) | __ |
| [getName(...)](method_getName.md) | __ |


## Public Representation
```php
final class CondorcetVote\CefWriter\Parameter\CustomParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly string $name;
    public protected(set) readonly string $value;

    // Methods
    public function __construct( string $name, string $value );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Parameter\CustomParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly string $name;
    public protected(set) readonly string $value;

    // Methods
    public function __construct( string $name, string $value );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```