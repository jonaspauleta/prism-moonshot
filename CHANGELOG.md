# Changelog

All notable changes to `laravel-ai-moonshot` are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-04-25

Initial public release.

### Added

- Moonshot AI (Kimi K2) provider for the official Laravel AI SDK (`laravel/ai`).
  Wraps Moonshot's OpenAI-compatible chat-completions endpoint
  (`POST https://api.moonshot.ai/v1/chat/completions`) and registers a native
  `TextProvider` / `TextGateway` via `AiManager::extend('moonshot', …)`.
- Text generation through `agent()`, `Ai::textProvider('moonshot')`, and
  `#[Provider('moonshot')]` agent classes.
- Streaming responses (`stream()`, `broadcast()`, `broadcastOnQueue()`) with
  `TextStart` / `TextDelta` / `TextEnd` SDK events.
- Tool calling (function tools). Provider-side tools throw
  `UnsupportedProviderToolException` — Moonshot has no provider-hosted tools.
- Image attachments: `Base64Image`, `RemoteImage`, `LocalImage`, `StoredImage`,
  and `Illuminate\Http\UploadedFile` (when MIME is `image/jpeg|png|gif|webp`).
- Document Q&A via Moonshot Files API (`/v1/files`). Server-side text
  extraction (PDF, DOC, XLSX, PPTX, code, EPUB, …) via the `MoonshotFiles`
  service and the ergonomic `InjectsMoonshotFiles` trait
  (`withMoonshotFile()`).
- Kimi **thinking mode** with `reasoning_content` deltas surfaced as
  `ReasoningStart` / `ReasoningDelta` / `ReasoningEnd` stream events.
  `ReasoningEnd` is guaranteed to fire before the first `TextStart`. Enabled
  per-call via `providerOptions(['thinking' => ['type' => 'enabled']])`;
  `keep: 'all'` preserves reasoning across multi-turn conversations on
  `kimi-k2.6`.
- Per-tier model overrides through
  `config('ai.providers.moonshot.models.text.{default,cheapest,smartest}')`.
  Defaults track Moonshot's public catalog (`kimi-k2.6` for default/smartest,
  `kimi-k2.5` for cheapest).
- Custom base URL via `config('ai.providers.moonshot.url')` for proxies and
  regional endpoints.
- Artisan commands: `ai:moonshot:models` (live `/v1/models` catalog) and
  `ai:moonshot:files` (list / delete uploads).
- Typed exceptions: `UnsupportedProviderToolException`,
  `UnsupportedAttachmentException`, `MoonshotFilesException`.
- Quality pipeline: Pest 3 / 4, PHPStan level max (no baseline), Pint,
  Rector. CI matrix on PHP 8.4 / 8.5 × Laravel 12 / 13. Weekly
  `catalog-drift` workflow polls `/v1/models` and opens an issue if any
  default tier ID disappears from the live catalog.

[1.0.0]: https://github.com/jonaspauleta/laravel-ai-moonshot/releases/tag/v1.0.0
