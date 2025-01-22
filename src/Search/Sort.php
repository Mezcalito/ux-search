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

namespace Mezcalito\UxSearchBundle\Search;

readonly class Sort
{
    public function __construct(
        private ?string $key,
        private string $label,
    ) {
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
