<?php

declare(strict_types=1);

namespace Jonaspauleta\PrismMoonshot\Maps;

use InvalidArgumentException;

/**
 * Maps Prism `withProviderOptions(['thinking' => ...])` input to the
 * Moonshot chat-completions `thinking` request body shape.
 *
 * Accepted input forms:
 * - `true` → `['type' => 'enabled']`
 * - `false` / `null` → null (omit the key)
 * - `['type' => 'enabled', 'keep' => 'all']` → passed through after validation
 *
 * @see https://platform.kimi.ai/docs/api/chat#body-one-of-0-thinking
 */
final class ThinkingMap
{
    /**
     * @return array{type: 'enabled'|'disabled', keep?: 'all'}|null
     */
    public static function map(mixed $value): ?array
    {
        if ($value === null || $value === false) {
            return null;
        }

        if ($value === true) {
            return ['type' => 'enabled'];
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException('Moonshot thinking option must be bool or array.');
        }

        $type = $value['type'] ?? 'enabled';

        if ($type !== 'enabled' && $type !== 'disabled') {
            $rendered = is_scalar($type) ? (string) $type : get_debug_type($type);

            throw new InvalidArgumentException(
                "Moonshot thinking.type must be 'enabled' or 'disabled', got '$rendered'.",
            );
        }

        /** @var array{type: 'enabled'|'disabled', keep?: 'all'} $payload */
        $payload = ['type' => $type];

        if (isset($value['keep'])) {
            if ($value['keep'] !== 'all') {
                throw new InvalidArgumentException(
                    "Moonshot thinking.keep currently only supports 'all'.",
                );
            }

            $payload['keep'] = 'all';
        }

        return $payload;
    }
}
