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

    public function getValues(): array
    {
        return $this->values;
    }

    public function setValues(array $values): static
    {
        $this->values = $values;

        return $this;
    }

    public function getCheckedValues(): array
    {
        return $this->checkedValues;
    }

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
