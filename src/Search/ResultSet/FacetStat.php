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

namespace Mezcalito\UxSearchBundle\Search\ResultSet;

readonly class FacetStat
{
    public function __construct(
        private string $property,
        private int|float $min,
        private int|float $max,
        private int|float|null $userMin = null,
        private int|float|null $userMax = null,
    ) {
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getMin(): float|int
    {
        return $this->min;
    }

    public function getMax(): float|int
    {
        return $this->max;
    }

    public function getUserMin(): float|int|null
    {
        return $this->userMin;
    }

    public function getUserMax(): float|int|null
    {
        return $this->userMax;
    }
}
