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

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->import(
        resource: [
            'path' => '../src/Controller/',
            'namespace' => 'Mezcalito\\UxSearchBundle\\Tests\\TestApplication\\Controller',
        ],
        type: 'attribute'
    );

    $routes->import('@LiveComponentBundle/config/routes.php')->prefix('/_components');

    if ('dev' === $routes->env()) {
        $routes->import('@WebProfilerBundle/Resources/config/routing/wdt.xml')->prefix('/_wdt');
        $routes->import('@WebProfilerBundle/Resources/config/routing/profiler.xml')->prefix('/_profiler');
    }
};
