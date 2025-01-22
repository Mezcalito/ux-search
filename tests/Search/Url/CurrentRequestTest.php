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

use Mezcalito\UxSearchBundle\Search\Url\CurrentRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CurrentRequestTest extends TestCase
{
    public function testConstructor(): void
    {
        $route = 'test_route';
        $parameters = ['param1' => 'value1', 'param2' => 'value2'];

        $currentRequest = new CurrentRequest($route, $parameters);

        $this->assertSame($route, $currentRequest->route);
        $this->assertSame($parameters, $currentRequest->parameters);
    }

    public function testFromRequest(): void
    {
        $route = 'test_route';
        $queryParams = ['param1' => 'value1', 'param2' => 'value2'];
        $attributes = ['_route' => $route, 'param3' => 'value3', '_controller' => 'test_controller'];

        $request = new Request($queryParams, [], $attributes);

        $currentRequest = CurrentRequest::fromRequest($request);

        $expectedParameters = ['param3' => 'value3', 'param1' => 'value1', 'param2' => 'value2'];

        $this->assertSame($route, $currentRequest->route);
        $this->assertSame($expectedParameters, $currentRequest->parameters);
    }

    public function testFromRequestFiltersUnderscoreParameters(): void
    {
        $route = 'test_route';
        $queryParams = ['param1' => 'value1'];
        $attributes = ['_route' => $route, '_controller' => 'test_controller', 'param2' => 'value2'];

        $request = new Request($queryParams, [], $attributes);

        $currentRequest = CurrentRequest::fromRequest($request);

        $expectedParameters = ['param2' => 'value2', 'param1' => 'value1'];

        $this->assertSame($route, $currentRequest->route);
        $this->assertSame($expectedParameters, $currentRequest->parameters);
    }
}
