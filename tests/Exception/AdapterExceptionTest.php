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

namespace Mezcalito\UxSearchBundle\Tests\Exception;

use Mezcalito\UxSearchBundle\Exception\AdapterException;
use PHPUnit\Framework\TestCase;

class AdapterExceptionTest extends TestCase
{
    public function testFactoryNotFoundRedactsDsnCredentials(): void
    {
        $exception = AdapterException::factoryNotFound('meilisearch://masterKey@meilisearch:7700');

        $this->assertSame('Factory with "meilisearch://***@meilisearch:7700" support not found', $exception->getMessage());
    }

    public function testFactoryNotFoundRedactsUserAndPassword(): void
    {
        $exception = AdapterException::factoryNotFound('algolia://appId:apiKey@algolia');

        $this->assertSame('Factory with "algolia://***@algolia" support not found', $exception->getMessage());
    }

    public function testFactoryNotFoundKeepsDsnWithoutCredentials(): void
    {
        $exception = AdapterException::factoryNotFound('doctrine://default');

        $this->assertSame('Factory with "doctrine://default" support not found', $exception->getMessage());
    }
}
