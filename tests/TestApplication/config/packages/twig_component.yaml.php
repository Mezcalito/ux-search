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
    $container->extension('twig_component', [
        'anonymous_template_directory' => 'components/',
        'defaults' => [
            'Mezcalito\UxSearchBundle\Tests\TestApplication\Twig\Components\\' => 'components/',
        ],
    ]);
};
