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

    public static function factoryNotFound(string $dsn): self
    {
        return new self(\sprintf('Factory with "%s" support not found', self::redactDsn($dsn)));
    }

    public static function invalidDsn(string $dsn): self
    {
        return new self(\sprintf('Invalid DSN "%s"', self::redactDsn($dsn)));
    }

    public static function searchFailed(?string $indexName, \Throwable $previous): self
    {
        return new self(\sprintf('Search failed for index "%s"', $indexName ?? ''), 0, $previous);
    }

    private static function redactDsn(string $dsn): string
    {
        return preg_replace('#(//)[^@/]+@#', '$1***@', $dsn) ?? $dsn;
    }
}
