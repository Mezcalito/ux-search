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

namespace Mezcalito\UxSearchBundle\Search\Filter;

class RangeFilter extends AbstractFilter
{
    public function __construct(
        string $property,
        private int|float|null $min = null,
        private int|float|null $max = null,
    ) {
        parent::__construct($property);
    }

    public function getMin(): int|float|null
    {
        return $this->min;
    }

    public function setMin(int|float|null $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): int|float|null
    {
        return $this->max;
    }

    public function setMax(int|float|null $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function hasValues(): bool
    {
        return $this->min || $this->max;
    }
}
