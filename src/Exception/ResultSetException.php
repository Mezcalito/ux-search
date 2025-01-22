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

class ResultSetException extends \RuntimeException
{
    public static function facetDistributionNotFound(string $property): self
    {
        return new self(\sprintf('Facet distribution "%s" is not found', $property));
    }

    public static function facetStatNotFound(string $property): self
    {
        return new self(\sprintf('Facet stat "%s" is not found', $property));
    }
}
