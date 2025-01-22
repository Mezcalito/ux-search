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

namespace Mezcalito\UxSearchBundle\Exception;

class SearchException extends \RuntimeException
{
    public static function facetNotConfigured(string $property): self
    {
        return new self(\sprintf('Facet "%s" is not configured', $property));
    }

    public static function nameNotFound(string $name): self
    {
        return new self(\sprintf('Search with name "%s" is not found', $name));
    }
}
