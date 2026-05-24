# AGENTS.md

Authoritative instructions for AI coding agents working on this repository.

## Project

PHP library that **generates** [Condorcet Election Format (CEF)](https://github.com/CondorcetVote/CondorcetElectionFormat) files with a friendly, low-level, streaming API.

## Language & tooling

- Modern PHP — minimum PHP **8.5** (see `composer.json`).
- Every symbol must be strictly typed (parameters, return types, properties).
- PHPStan must pass (`vendor/bin/phpstan analyse`).
- PHPDoc on every public symbol. Use PHPDoc for shapes PHP cannot express (`list<string>`, `array<string, int>`, etc.).
- All identifiers, comments, docs, commit messages, exceptions: **English only**.
- Comments only when the *why* is non-obvious. Code should be self-documenting.
- Follow `.php-cs-fixer.dist.php` (run `vendor/bin/php-cs-fixer fix`).

## Testing

- Use **Pest 4** for everything. Tests live under `tests/`.
- Aim for thorough coverage: every public method, every exception path, every format option.
- Run with `vendor/bin/pest`.

## Documentation

- `README.md` is the project's public documentation. **Always keep it up to date.**
  Every change that adds, removes, or modifies a public API surface (new method,
  new class, renamed method, changed signature, new exception type, new
  runtime requirement, new behaviour) MUST be reflected in `README.md` in the
  same change set. Treat the README as part of the API: out-of-date docs are
  a bug.
- Public examples in the README must actually run as written — when in doubt,
  copy them into a scratch script and verify before committing.
- Do **not** put project instructions inside `.github/` — they belong in this file.

## Format reference

The CEF specification at <https://github.com/CondorcetVote/CondorcetElectionFormat> is the source of truth. Read it carefully and respect every rule it defines, including reserved characters (`> = ; , # / * ^`), line-ending conventions, and ordering constraints.

## Scope of this library

- **Generates** valid CEF — never parses it.
- **Streaming**: each call writes immediately to the target (`SplFileObject` or string by reference). Nothing is buffered in memory beyond a single line, and emitted content cannot be edited afterwards.
- **Guarantees format validity**: invalid input must throw a `CefFormatException`. The library does **not** verify election semantics (e.g. whether a candidate listed in a vote also appears in `#/Candidates:`).
- Targets are either a string (by reference), an `SplFileObject`, an `SplFileInfo`, or a path string; all must be writable.
- Parameters must be written before votes; once a vote is added, parameters are locked.
