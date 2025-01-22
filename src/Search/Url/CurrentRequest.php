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
    public function __construct(
        public string $route,
        public array $parameters,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $parameters = array_filter(array_merge($request->attributes->all(), $request->query->all()), fn ($key) => !str_starts_with((string) $key, '_'), \ARRAY_FILTER_USE_KEY);

        return new self($request->attributes->get('_route'), $parameters);
    }
}
