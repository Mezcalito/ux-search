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

use Mezcalito\UxSearchBundle\Exception\UnsupportedFilterException;
use PHPUnit\Framework\TestCase;

class UnsupportedFilterExceptionTest extends TestCase
{
    public function testFilterNotSupported(): void
    {
        $exception = UnsupportedFilterException::filterNotSupported('App\Search\Filter\CustomFilter');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Facet filter "App\Search\Filter\CustomFilter" not supported', $exception->getMessage());
    }
}
