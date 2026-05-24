> CondorcetVote \ [CefWriter](../../readme.md) \ **NumberOfSeatsParameter**
# Class NumberOfSeatsParameter
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Parameter/NumberOfSeatsParameter.php#L12)

## Description
`#/Number of Seats:` parameter — strictly positive integer.
## Elements

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [seats(...)](property_seats.md) | __ |

### Public Methods
| Method Name | Description |
| ------------- | ------------- |
| [__construct(...)](method___construct.md) | __ |
| [getFormattedValue(...)](method_getFormattedValue.md) | __ |
| [getName(...)](method_getName.md) | __ |


## Public Representation
```php
final class CondorcetVote\CefWriter\Parameter\NumberOfSeatsParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly int $seats;

    // Methods
    public function __construct( int $seats );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```

## Full Representation
```php
final class CondorcetVote\CefWriter\Parameter\NumberOfSeatsParameter implements CondorcetVote\CefWriter\Parameter\ParameterInterface
{

    // Properties
    public protected(set) readonly int $seats;

    // Methods
    public function __construct( int $seats );
    public function getFormattedValue( [ bool $autoFormat = true ] ): string;
    public function getName( ): string;

}
```