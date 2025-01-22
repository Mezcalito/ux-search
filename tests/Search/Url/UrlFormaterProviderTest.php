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

namespace Mezcalito\UxSearchBundle\Tests\Search\Url;

use Mezcalito\UxSearchBundle\Exception\UrlFormaterException;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterProvider;
use PHPUnit\Framework\TestCase;

final class UrlFormaterProviderTest extends TestCase
{
    public function testGetUrlFormaterReturnsCorrectFormater(): void
    {
        $mockFormater1 = $this->createMock(UrlFormaterInterface::class);
        $mockFormater2 = $this->createMock(UrlFormaterInterface::class);

        $formaters = [
            'App\\UrlFormaterOne' => $mockFormater1,
            'App\\UrlFormaterTwo' => $mockFormater2,
        ];

        $provider = new UrlFormaterProvider($formaters);

        $this->assertSame($mockFormater1, $provider->getUrlFormater('App\\UrlFormaterOne'));
        $this->assertSame($mockFormater2, $provider->getUrlFormater('App\\UrlFormaterTwo'));
    }

    public function testGetUrlFormaterThrowsExceptionIfNotFound(): void
    {
        $formaters = [];

        $provider = new UrlFormaterProvider($formaters);

        $this->expectException(UrlFormaterException::class);

        $provider->getUrlFormater('App\\NonExistentFormater');
    }
}
