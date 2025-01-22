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

class DoctrineAdapterException extends \RuntimeException
{
    public static function isNotOrmManager(string $name): self
    {
        return new self(\sprintf('Manager "%s" is not a EntityManager', $name));
    }
}
