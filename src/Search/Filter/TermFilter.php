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

class TermFilter extends AbstractFilter
{
    public function __construct(string $property, private array $values = [])
    {
        parent::__construct($property);
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

    public function hasValue(string $value): bool
    {
        return \in_array($value, $this->values);
    }

    public function hasValues(): bool
    {
        return [] !== $this->values;
    }

    public function addValue(string $value): void
    {
        $this->values[] = $value;
    }

    public function removeValue(string $value): void
    {
        if (($key = array_search($value, $this->values, true)) !== false) {
            unset($this->values[$key]);
        }
    }

    public function toggleValue(string $value): void
    {
        if ($this->hasValue($value)) {
            $this->removeValue($value);
        } else {
            $this->addValue($value);
        }
    }
}
