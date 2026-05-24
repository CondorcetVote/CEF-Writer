<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\{Cef, CommentLine, VoteLine};
use CondorcetVote\CefWriter\Parameter\{CandidatesParameter, ImplicitRankingParameter, WeightAllowedParameter};
use CondorcetVote\CefWriter\Exception\{InvalidValueException, InvalidWriterStateException};

it('appends to the caller string by reference', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addComment(new CommentLine('first line'));
    $cef->addEmptyLine();

    expect($buffer())->toBe("# first line\n\n");
});

it('throws when neither file nor string is provided', function (): void {
    new Cef;
})->throws(InvalidWriterStateException::class, 'Exactly one');

it('throws when both file and string are provided', function (): void {
    $buffer = '';

    new Cef(file: '/tmp/whatever', string: $buffer);
})->throws(InvalidWriterStateException::class, 'Exactly one');

it('reproduces the spec\'s implicit-ranking example almost verbatim', function (): void {
    [$cef, $buffer] = makeStringCef();
    $cef->autoFormat = true;

    $cef->addComment(new CommentLine('My beautiful election'));
    $cef->addParameter(new CandidatesParameter(['Candidate A', 'Candidate B', 'Candidate C']));
    $cef->addParameter(new ImplicitRankingParameter(true));
    $cef->addParameter(new WeightAllowedParameter(true));
    $cef->addComment(new CommentLine('Here the votes datas:'));

    $cef->addVote(new VoteLine(
        ranking: [['Candidate A'], ['Candidate B'], ['Candidate C']],
        quantifier: 42,
    ));
    $cef->addVote(new VoteLine(
        ranking: [['Candidate A'], ['Candidate B'], ['Candidate C']],
        tags: ['julien@condorcet.vote', 'signature:55073db57b0a859911'],
    ));
    $cef->addVote(new VoteLine(
        ranking: [['Candidate C'], ['Candidate A', 'Candidate B']],
        weight: 7,
        quantifier: 8,
    ));

    expect($buffer())->toContain('#/Candidates: Candidate A ; Candidate B ; Candidate C');
    expect($buffer())->toContain('#/Implicit Ranking: true');
    expect($buffer())->toContain('#/Weight Allowed: true');
    expect($buffer())->toContain('Candidate A > Candidate B > Candidate C * 42');
    expect($buffer())->toContain('julien@condorcet.vote, signature:55073db57b0a859911 || Candidate A > Candidate B > Candidate C');
    expect($buffer())->toContain('Candidate C > Candidate A = Candidate B ^7 * 8');
});

it('uses compact formatting when autoFormat is off', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: false);

    $cef->addParameter(new CandidatesParameter(['A', 'B']));
    $cef->addVote(new VoteLine([['A'], ['B']]));

    expect($buffer())->toBe("#/Candidates:A;B\nA>B\n");
});

it('inserts an automatic blank line between params and votes when autoFormat is on', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addParameter(new CandidatesParameter(['A', 'B']));
    $cef->addVote(new VoteLine([['A'], ['B']]));

    expect($buffer())->toBe("#/Candidates: A ; B\n\nA > B\n");
});

it('does NOT insert an automatic blank line between params and votes when autoFormat is off', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: false);

    $cef->addParameter(new CandidatesParameter(['A', 'B']));
    $cef->addVote(new VoteLine([['A'], ['B']]));

    expect($buffer())->toBe("#/Candidates:A;B\nA>B\n");
});

it('does not insert an auto separator when no parameter was written', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addVote(new VoteLine([['A']]));

    expect($buffer())->toBe("A\n");
});

it('inserts the auto separator only once even with multiple vote calls', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: true);

    $cef->addParameter(new CandidatesParameter(['A', 'B']));
    $cef->addVote(new VoteLine([['A']]));
    $cef->addVote(new VoteLine([['B']]));

    expect(substr_count($buffer(), "\n\n"))->toBe(1);
});

it('locks parameter writing after the first vote', function (): void {
    [$cef] = makeStringCef();

    $cef->addParameter(new CandidatesParameter(['A']));
    $cef->addVote(new VoteLine([['A']]));
    $cef->addParameter(new ImplicitRankingParameter(true));
})->throws(InvalidWriterStateException::class, 'before any vote');

it('allows comments and empty lines on either side of the vote boundary', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addComment(new CommentLine('preamble'));
    $cef->addParameter(new CandidatesParameter(['A']));
    $cef->addComment(new CommentLine('between'));
    $cef->addEmptyLine();
    $cef->addVote(new VoteLine([['A']]));
    $cef->addComment(new CommentLine('trailing'));

    expect($buffer())->toContain('# preamble');
    expect($buffer())->toContain('# between');
    expect($buffer())->toContain('# trailing');
});

it('renders a vote with an inline comment', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addVote(new VoteLine([['A']], inlineComment: 'my note'));

    expect($buffer())->toBe("A # my note\n");
});

it('renders an inline comment compactly when autoFormat is off', function (): void {
    [$cef, $buffer] = makeStringCef(autoFormat: false);

    $cef->addVote(new VoteLine([['A']], inlineComment: 'note'));

    expect($buffer())->toBe("A#note\n");
});

it('exposes addCommentLine() as a shortcut that builds a CommentLine for you', function (): void {
    [$cef, $buffer] = makeStringCef();

    $cef->addCommentLine('hello world');

    expect($buffer())->toBe("# hello world\n");
});

it('addCommentLine() validates the text just like CommentLine would', function (): void {
    [$cef] = makeStringCef();

    $cef->addCommentLine("multi\nline");
})->throws(InvalidValueException::class);

it('returns $this from every add method to support chaining', function (): void {
    [$cef, $buffer] = makeStringCef();

    $result = $cef
        ->addParameter(new CandidatesParameter(['A']))
        ->addEmptyLine()
        ->addComment(new CommentLine('x'))
        ->addVote(new VoteLine([['A']]));

    expect($result)->toBe($cef);
});
