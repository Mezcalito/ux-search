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

namespace Mezcalito\UxSearchBundle\Adapter;

use Mezcalito\UxSearchBundle\Exception\AdapterException;

readonly class AdapterProvider
{
    public function __construct(
        private string $defaultAdapterName,
        private array $adapterConfiguration,
        private iterable $factories,
    ) {
    }

    public function getAdapter(?string $name = null): AdapterInterface
    {
        if (null === $name) {
            $name = $this->defaultAdapterName;
        }

        if (!\array_key_exists($name, $this->adapterConfiguration)) {
            throw AdapterException::configurationNotFound($name);
        }

        $dsn = $this->adapterConfiguration[$name]['dsn'];

        foreach ($this->factories as $factory) {
            if ($factory->support($dsn)) {
                return $factory->createAdapter($dsn);
            }
        }

        throw AdapterException::factoryNotFound($dsn);
    }
}
