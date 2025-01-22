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
    $container->extension('mezcalito_ux_search', [
        'default_adapter' => 'meilisearch',
        'adapters' => [
            'meilisearch' => 'meilisearch://secret@uxsearch_meilisearch:7700?tls=false',
            'doctrine' => 'doctrine://default',
        ],
    ]);
};
