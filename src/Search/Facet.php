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

readonly class Facet
{
    public function __construct(
        private string $property,
        private string $label,
        private ?string $displayComponent = null,
        private array $props = [],
    ) {
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDisplayComponent(): ?string
    {
        return $this->displayComponent;
    }

    public function getProps(): array
    {
        return $this->props;
    }
}
