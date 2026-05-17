# AGENTS.md

This file provides basic guidance for coding agents working in this repository.

## Scope

- Applies to the entire repository.

## Project overview

- TYPO3 extension providing ViewHelpers for rendering adaptive/responsive images.
- Main PHP code lives in `Classes/`.
- TypoScript configuration lives in `Configuration/TypoScript/`.
- Templates and assets live in `Resources/`.
- Unit tests live in `Tests/Unit/`.
- Acceptance tests live in `Tests/Acceptance/` and use Codeception.

## Preferred workflow

- Keep changes focused and minimal.
- Follow existing patterns before introducing new abstractions.
- Do not modify unrelated files.
- Prefer repository tooling and documented commands over ad hoc scripts.

## Setup and common commands

Install dependencies targeting a specific TYPO3 version (required before running any checks or tests):

```bash
composer require typo3/minimal:'^13.4'   # or ^14.3
```

Run checks and tests:

- Run PHP CS Fixer lint checks: `composer php:lint`
- Apply PHP CS Fixer fixes: `composer php:fix`
- Run TypoScript lint: `composer ts:lint`
- Run static analysis: `composer phpstan`
- Run all static checks: `composer ci:static`
- Run unit tests: `composer tests:unit`
- Run acceptance tests: `composer tests:acceptance`
- Build documentation: `composer build:doc`

Unit tests require environment variables to be set:

```bash
export TYPO3_PATH_ROOT=$PWD/.Build/public
export TYPO3_PATH_APP=$PWD
composer tests:unit
```

## Coding conventions

- Follow the TYPO3 coding standard for PHP (enforced via PHP CS Fixer).
- Target PHP 8.2-compatible code (minimum supported version).
- Prefer short array syntax: `[]`.
- Use strict types (`declare(strict_types=1)`) in all PHP files.
- Keep PHPDoc aligned with the existing codebase style.
- PHPUnit data providers must be declared `static`.
- Do not use `registerTagAttribute()` — it was removed in Fluid v5 (TYPO3 v14). Use `registerArgument()` instead and apply tag attributes manually in `initialize()`.

## Validation

- For code changes, run the smallest relevant existing checks before finishing.
- Always run `composer phpstan` and `composer php:lint` after PHP changes.
- Run `composer ts:lint` after TypoScript changes.
- Run `composer tests:unit` after any PHP change.
- Do not introduce new build tools or test frameworks.
- If only documentation changes are made, tests are not required.

## Notes for agents

- Check `README.md` and `Documentation/` when behavior or conventions are unclear.
- The extension supports TYPO3 ^13.4 and ^14.3 — do not break either version.
- PHP minimum version is 8.2; do not use PHP 8.3+ syntax.
- Preserve user changes in a dirty working tree.
- Call out assumptions clearly when repository behavior is ambiguous.
- Do not lie or fantasize. If you are unsure, ask for clarification.
- If you don't know how to do something, ask for clarification.
- Before doing possible destructive actions, ask for confirmation. 

## Accuracy and avoiding hallucinations

- **Tie every factual claim to a concrete source.** When making an assertion, name the specific
  file, line number, issue URL, or official doc page it comes from — not just in a sources
  section at the end, but inline with the claim itself.

- **Verify codebase claims in the actual code first.** For any statement about how TYPO3,
  Symfony, Doctrine, or any installed dependency behaves, prefer `grep`/`view` on the
  vendor source over web search results. If a claim can be verified with a line number, it
  should be.

- **Treat web search results with extra skepticism.** The web search tool returns
  AI-generated summaries, not raw sources. Those summaries can themselves hallucinate.
  Use web search to find links to authoritative pages, then `web_fetch` those pages directly
  to verify the actual content before citing them.

- **Avoid vague aggregating language without a named source.** Phrases like "community
  consensus", "it is recommended", "best practice", or "it is well known" without a specific
  citation are a red flag. Either name the source or drop the claim.

- **Distinguish confidence levels explicitly.** Use clear qualifiers:
    - *Verified in code* — confirmed with a file and line number in this repo or vendor
    - *From official docs* — confirmed by fetching the actual documentation page
    - *From web search (unverified)* — found via search but not independently confirmed
    - *Inferred* — logical conclusion from verified facts, not directly stated anywhere
