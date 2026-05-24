<?php

declare(strict_types=1);

use CondorcetVote\CefWriter\CommentLine;
use CondorcetVote\CefWriter\Exception\CefFormatException;

it('prefixes the text with "#" and a space when autoFormat is on', function (): void {
    $comment = new CommentLine('hello world');

    expect($comment->format(true))->toBe('# hello world');
});

it('omits the leading space when autoFormat is off', function (): void {
    $comment = new CommentLine('hello world');

    expect($comment->format(false))->toBe('#hello world');
});

it('keeps a user-supplied leading space rather than doubling it', function (): void {
    $comment = new CommentLine(' hello');

    expect($comment->format(true))->toBe('# hello');
    expect($comment->format(false))->toBe('# hello');
});

it('renders a lone "#" for an empty comment text', function (): void {
    $comment = new CommentLine('');

    expect($comment->format(true))->toBe('#');
    expect($comment->format(false))->toBe('#');
});

it('rejects a multi-line comment text', function (): void {
    new CommentLine("one\ntwo");
})->throws(CefFormatException::class);
