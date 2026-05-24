> CondorcetVote \ [CefWriter](../../readme.md) \ **StandardParameter**
# Enum StandardParameter
> [Read it at source](https://github.com/CondorcetVote/CEF-Writer/blob/main/src/src/Parameter/StandardParameter.php#L13)

## Description
Enumeration of the parameter names defined by the CEF specification.

The string value of each case is the exact, case-correct name that must
appear after the `#/` prefix in the generated file.
## Elements

### Public Constants
| Constant Name | Signature | Description |
| ------------- | ------------- | ------------- |
| Candidates | `public const Candidates = \CondorcetVote\CefWriter\Parameter\StandardParameter::Candidates` | __ |
| ImplicitRanking | `public const ImplicitRanking = \CondorcetVote\CefWriter\Parameter\StandardParameter::ImplicitRanking` | __ |
| NumberOfSeats | `public const NumberOfSeats = \CondorcetVote\CefWriter\Parameter\StandardParameter::NumberOfSeats` | __ |
| VotingMethods | `public const VotingMethods = \CondorcetVote\CefWriter\Parameter\StandardParameter::VotingMethods` | __ |
| WeightAllowed | `public const WeightAllowed = \CondorcetVote\CefWriter\Parameter\StandardParameter::WeightAllowed` | __ |

### Public Properties
| Property Name | Description |
| ------------- | ------------- |
| [name(...)](property_name.md) | __ |
| [value(...)](property_value.md) | __ |


## Public Representation
```php
enum CondorcetVote\CefWriter\Parameter\StandardParameter: string implements UnitEnum, BackedEnum
{
    case Candidates = "Candidates";
    case NumberOfSeats = "Number of Seats";
    case ImplicitRanking = "Implicit Ranking";
    case VotingMethods = "Voting Methods";
    case WeightAllowed = "Weight Allowed";
    // Constants
    public const Candidates = \CondorcetVote\CefWriter\Parameter\StandardParameter::Candidates;
    public const ImplicitRanking = \CondorcetVote\CefWriter\Parameter\StandardParameter::ImplicitRanking;
    public const NumberOfSeats = \CondorcetVote\CefWriter\Parameter\StandardParameter::NumberOfSeats;
    public const VotingMethods = \CondorcetVote\CefWriter\Parameter\StandardParameter::VotingMethods;
    public const WeightAllowed = \CondorcetVote\CefWriter\Parameter\StandardParameter::WeightAllowed;

    // Properties
    public protected(set) readonly string $name;
    public protected(set) readonly string $value;

}
```

## Full Representation
```php
enum CondorcetVote\CefWriter\Parameter\StandardParameter: string implements UnitEnum, BackedEnum
{
    case Candidates = "Candidates";
    case NumberOfSeats = "Number of Seats";
    case ImplicitRanking = "Implicit Ranking";
    case VotingMethods = "Voting Methods";
    case WeightAllowed = "Weight Allowed";
    // Constants
    public const Candidates = \CondorcetVote\CefWriter\Parameter\StandardParameter::Candidates;
    public const ImplicitRanking = \CondorcetVote\CefWriter\Parameter\StandardParameter::ImplicitRanking;
    public const NumberOfSeats = \CondorcetVote\CefWriter\Parameter\StandardParameter::NumberOfSeats;
    public const VotingMethods = \CondorcetVote\CefWriter\Parameter\StandardParameter::VotingMethods;
    public const WeightAllowed = \CondorcetVote\CefWriter\Parameter\StandardParameter::WeightAllowed;

    // Properties
    public protected(set) readonly string $name;
    public protected(set) readonly string $value;

}
```