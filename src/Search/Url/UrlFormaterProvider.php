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

namespace Mezcalito\UxSearchBundle\Search\Url;

use Mezcalito\UxSearchBundle\Exception\UrlFormaterException;
use Psr\Container\ContainerInterface;

readonly class UrlFormaterProvider
{
    /**
     * @param ContainerInterface|iterable<string, UrlFormaterInterface> $formaters
     */
    public function __construct(
        private ContainerInterface|iterable $formaters,
    ) {
    }

    public function getUrlFormater(string $fqcn): UrlFormaterInterface
    {
        if ($this->formaters instanceof ContainerInterface) {
            if (!$this->formaters->has($fqcn)) {
                throw UrlFormaterException::urlFormaterNotFound($fqcn);
            }

            /* @var UrlFormaterInterface */
            return $this->formaters->get($fqcn);
        }

        foreach ($this->formaters as $urlFormaterName => $formater) {
            if ($fqcn === $urlFormaterName) {
                return $formater;
            }
        }

        throw UrlFormaterException::urlFormaterNotFound($fqcn);
    }
}
