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

class AdapterException extends \RuntimeException
{
    public static function configurationNotFound(string $name): self
    {
        return new self(\sprintf('Configuration for name "%s" is not found', $name));
    }

    public static function factoryNotFound(string $name): self
    {
        return new self(\sprintf('Factory with "%s" support not found', $name));
    }
}
