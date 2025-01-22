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

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    if ('dev' === $container->env()) {
        $container->extension('web_profiler', [
            'toolbar' => true,
            'intercept_redirects' => false,
        ]);
        $container->extension('framework', [
            'profiler' => [
                'only_exceptions' => false,
                'collect_serializer_data' => true,
            ],
        ]);
    }

    if ('test' === $container->env()) {
        $container->extension('web_profiler', [
            'toolbar' => false,
            'intercept_redirects' => false,
        ]);
        $container->extension('framework', [
            'profiler' => [
                'collect' => false,
            ],
        ]);
    }
};
