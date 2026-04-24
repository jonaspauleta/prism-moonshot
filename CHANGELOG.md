# Changelog

All notable changes to `prism-moonshot` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.1] - 2026-04-24

### Added

- Dedicated `prism.stream_timeout` config key for the streaming HTTP client. When set, `Moonshot::stream()` applies it as both `timeout` and `connect_timeout`, overriding the default `prism.request_timeout`. Lets callers extend the ceiling for long `thinking`-mode streams without inflating the per-request timeout used by text/structured calls. No behavior change when unset.

[0.2.1]: https://github.com/jonaspauleta/prism-moonshot/releases/tag/v0.2.1

## [0.2.0] - 2026-04-24

### Added

- **Thinking mode** support via `withProviderOptions(['thinking' => ...])`. Accepts `true` (shorthand for `['type' => 'enabled']`), or the full Moonshot shape `['type' => 'enabled'|'disabled', 'keep' => 'all']`. The `keep: all` flag is `kimi-k2.6`-only and preserves reasoning across multi-turn conversations.
- New `Maps\ThinkingMap` value mapper with input validation for `type` and `keep`.
- Tests covering all thinking input forms + invalid-input rejection paths.
- Documented Kimi thinking schema in README with both Prism standalone and Laravel AI agent examples.

[0.2.0]: https://github.com/jonaspauleta/prism-moonshot/releases/tag/v0.2.0

## [0.1.0] - 2026-04-23

### Added

- Initial Moonshot AI (Kimi K2) provider for Prism PHP.
- Bridge to the Laravel AI SDK via `MoonshotGateway` (extends `PrismGateway`).
- Text, streaming, structured/JSON-mode, and tool-calling support.
- `reasoning_content` deltas surfaced as Prism `ThinkingEvent`s.
- Image input via OpenAI-compatible `image_url` content parts.
- Pest 4 + PHPStan level max + Pint + Rector quality pipeline.
- GitHub Actions workflow.

[Unreleased]: https://github.com/jonaspauleta/prism-moonshot/compare/v0.2.1...HEAD
[0.1.0]: https://github.com/jonaspauleta/prism-moonshot/releases/tag/v0.1.0
