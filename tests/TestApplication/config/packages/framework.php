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
    $container->extension('framework', [
        'secret' => 'secret',
        'session' => [
            'handler_id' => null,
        ],
        'asset_mapper' => [
            'paths' => [
                'assets/',
            ],
        ],
    ]);

    if ('test' === $container->env()) {
        $container->extension('framework', [
            'session' => [
                'storage_factory_id' => 'session.storage.factory.mock_file',
            ],
            'test' => true,
        ]);
    }
};
