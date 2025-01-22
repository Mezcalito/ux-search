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

readonly class UrlFormaterProvider
{
    public function __construct(
        private iterable $formaters,
    ) {
    }

    public function getUrlFormater(string $fqcn): UrlFormaterInterface
    {
        /** @var UrlFormaterInterface $formater */
        foreach ($this->formaters as $urlFormaterName => $formater) {
            if ($fqcn === $urlFormaterName) {
                return $formater;
            }
        }

        throw UrlFormaterException::urlFormaterNotFound($fqcn);
    }
}
