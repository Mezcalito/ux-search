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

class FacetTermDistribution
{
    private string $property;

    /** @var array<mixed, int> */
    private array $values = [];

    /** @var array<int, mixed> */
    private array $checkedValues = [];

    public function getProperty(): string
    {
        return $this->property;
    }

    public function setProperty(string $property): static
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return array<mixed, int>
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @param array<mixed, int> $values
     */
    public function setValues(array $values): static
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return array<int, mixed>
     */
    public function getCheckedValues(): array
    {
        return $this->checkedValues;
    }

    /**
     * @param array<int, mixed> $checkedValues
     */
    public function setCheckedValues(array $checkedValues): static
    {
        $this->checkedValues = $checkedValues;

        return $this;
    }

    public function isChecked(mixed $value): bool
    {
        return \in_array($value, $this->checkedValues);
    }
}
