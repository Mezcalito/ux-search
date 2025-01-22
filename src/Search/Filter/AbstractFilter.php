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

abstract class AbstractFilter implements FilterInterface
{
    public function __construct(private readonly ?string $property)
    {
    }

    public function getProperty(): ?string
    {
        return $this->property;
    }
}
