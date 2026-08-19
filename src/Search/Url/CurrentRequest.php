<?php

/*
 * This file is part of the UxSearch project.
 *
 * (c) Mezcalito (https://www.mezcalito.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Search\Url;

use Symfony\Component\HttpFoundation\Request;

readonly class CurrentRequest
{
    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        public string $route,
        public array $parameters,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        // Request attributes may hold resolved entities or other business objects:
        // only raw route parameters and the query string may be exposed to the client.
        $parameters = array_filter(
            array_merge($request->attributes->all('_route_params'), $request->query->all()),
            static fn ($value, $key) => !str_starts_with((string) $key, '_') && (null === $value || \is_scalar($value) || \is_array($value)),
            \ARRAY_FILTER_USE_BOTH
        );

        return new self((string) $request->attributes->get('_route', ''), $parameters);
    }
}
