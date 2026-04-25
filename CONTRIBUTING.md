# Contributing

Thanks for considering a contribution to `laravel-ai-moonshot`. This is a small, focused package — keep PRs small and focused too.

## Workflow

1. **Open an issue first for non-trivial changes.** Bug fixes with a failing test attached can go straight to PR; new features, API changes, or refactors should be discussed first.
2. Fork, branch off `main`, and use a descriptive branch name (e.g. `fix/streaming-tool-call-recursion`).
3. Add Pest tests for any new behavior. `tests/Feature/MoonshotStreamTest.php` is the template for HTTP-faked gateway tests.
4. Run the full quality pipeline locally before pushing:

   ```bash
   composer quality   # rector --dry-run + pint + phpstan + pest
   ```

   CI runs the same set on every push and PR. PHPStan is at `level: max` with no baseline — keep it that way.
5. Use [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, `docs:`, `refactor:`, `test:`, `chore:`, `ci:`). Breaking changes use `feat!:` / `fix!:`.
6. Update `CHANGELOG.md` under the `[Unreleased]` section. Group entries under `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.
7. Open the PR against `main`. Fill out the PR template checklist.

## Code style

- `declare(strict_types=1);` in every file.
- `final` classes by default.
- No `data_get()` on Moonshot HTTP responses — narrow `mixed` inline at the read site. See `CLAUDE.md` § Type-safety.
- Pint preset is `laravel` with the rules in `pint.json`. Run `composer format` to apply.

## Branch / release model

- `main` is always shippable.
- Versioning follows [SemVer](https://semver.org/). Breaking changes bump the major.
- Tags are cut after the changelog entry is moved out of `[Unreleased]` into a dated version block.

## Reporting bugs

Use the bug report template under "New issue". Include the Moonshot model ID, a minimal reproduction, and the full stack trace.

## Security

Do **not** open public issues for security vulnerabilities. See [SECURITY.md](SECURITY.md).
