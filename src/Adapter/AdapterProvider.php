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

class AdapterProvider
{
    /** @var array<string, AdapterInterface> */
    private array $adapters = [];

    /**
     * @param array<string, array<string, mixed>> $adapterConfiguration
     * @param iterable<AdapterFactoryInterface>   $factories
     */
    public function __construct(
        private readonly string $defaultAdapterName,
        private readonly array $adapterConfiguration,
        private readonly iterable $factories,
    ) {
    }

    public function getAdapter(?string $name = null): AdapterInterface
    {
        $name ??= $this->defaultAdapterName;

        if (isset($this->adapters[$name])) {
            return $this->adapters[$name];
        }

        if (!\array_key_exists($name, $this->adapterConfiguration)) {
            throw AdapterException::configurationNotFound($name);
        }

        $dsn = $this->adapterConfiguration[$name]['dsn'] ?? null;
        if (!\is_string($dsn) || '' === $dsn) {
            throw AdapterException::invalidDsn((string) $dsn);
        }

        foreach ($this->factories as $factory) {
            if ($factory->support($dsn)) {
                return $this->adapters[$name] = $factory->createAdapter($dsn);
            }
        }

        throw AdapterException::factoryNotFound($dsn);
    }
}
