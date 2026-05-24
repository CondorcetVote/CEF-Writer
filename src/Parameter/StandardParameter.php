<?php

declare(strict_types=1);

namespace CondorcetVote\CondorcetElectionFormatGenerator\Parameter;

/**
 * Enumeration of the parameter names defined by the CEF specification.
 *
 * The string value of each case is the exact, case-correct name that must
 * appear after the `#/` prefix in the generated file.
 */
enum StandardParameter: string
{
    case Candidates = 'Candidates';
    case NumberOfSeats = 'Number of Seats';
    case ImplicitRanking = 'Implicit Ranking';
    case VotingMethods = 'Voting Methods';
    case WeightAllowed = 'Weight Allowed';
}
