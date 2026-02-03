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

class UnsupportedFilterException extends \RuntimeException
{
    public static function filterNotSupported(string $filterClass): self
    {
        return new self(\sprintf('Facet filter "%s" not supported', $filterClass));
    }
}
