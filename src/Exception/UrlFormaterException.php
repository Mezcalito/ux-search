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

class UrlFormaterException extends \RuntimeException
{
    public static function urlFormaterNotFound(string $fqcn): self
    {
        return new self(\sprintf('Url Formater with "%s" FQCN not found', $fqcn));
    }
}
