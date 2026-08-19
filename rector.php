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

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Symfony72\Rector\StmtsAwareInterface\PushRequestToRequestStackConstructorRector;
use Rector\Symfony\Symfony73\Rector\Class_\GetFiltersAndFunctionsToAsTwigAttributeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
    ])
    ->withPhpSets(php83: true)
    ->withComposerBased(symfony: true, phpunit: true)
    ->withSkip([
        __DIR__.'/tests/TestApplication/*',
        // Requires the twig.attribute_extension registration, not available on all supported versions
        GetFiltersAndFunctionsToAsTwigAttributeRector::class,
        // RequestStack constructor argument is not available on Symfony 6.4
        PushRequestToRequestStackConstructorRector::class,
    ]);
