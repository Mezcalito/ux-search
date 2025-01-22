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

namespace Mezcalito\UxSearchBundle;

use Mezcalito\UxSearchBundle\Adapter\AdapterFactoryInterface;
use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\DependencyInjection\AdapterFactoryPass;
use Mezcalito\UxSearchBundle\DependencyInjection\RegisterSearchPass;
use Mezcalito\UxSearchBundle\DependencyInjection\UrlFormaterPass;
use Mezcalito\UxSearchBundle\Search\Url\UrlFormaterInterface;
use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class MezcalitoUxSearchBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $definition->rootNode();
        $rootNode
            ->children()
                ->scalarNode('default_adapter')->defaultValue('default')->end()
            ->end()
            ->children()
                ->arrayNode('adapters')
                    ->isRequired()
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(fn (string $v): array => ['dsn' => $v])
                        ->end()
                        ->children()
                            ->scalarNode('dsn')->isRequired()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $builder->setParameter('mezcalito_ux_search.default_adapter', $config['default_adapter']);
        $builder->setParameter('mezcalito_ux_search.adapters', $config['adapters']);

        $builder->registerAttributeForAutoconfiguration(AsSearch::class, static function (ChildDefinition $definition, AsSearch $attribute, \Reflector $reflector) {
            $tagAttributes = get_object_vars($attribute);
            if (null === $tagAttributes['name'] && $reflector instanceof \ReflectionClass) {
                $tagAttributes['name'] = strtolower(str_replace('Search', '', $reflector->getShortName()));
            }

            $definition->addTag('mezcalito_ux_search.search', $tagAttributes);
        });

        $builder->registerForAutoconfiguration(AdapterFactoryInterface::class)->addTag('mezcalito_ux_search.adapter_factory');
        $builder->registerForAutoconfiguration(UrlFormaterInterface::class)->addTag('mezcalito_ux_search.url_formater');
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterSearchPass());
        $container->addCompilerPass(new AdapterFactoryPass());
        $container->addCompilerPass(new UrlFormaterPass());
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('twig_component', [
            'defaults' => [
                'Mezcalito\UxSearchBundle\Twig\Components\\' => [
                    'template_directory' => '@MezcalitoUxSearch/',
                    'name_prefix' => 'Mezcalito:UxSearch',
                ],
            ],
        ]);

        if ($this->isAssetMapperAvailable($builder)) {
            $builder->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        __DIR__.'/../assets/dist' => '@mezcalito/ux-search-bundle',
                    ],
                ],
            ]);
        }
    }

    private function isAssetMapperAvailable(ContainerBuilder $builder): bool
    {
        if (!interface_exists(AssetMapperInterface::class)) {
            return false;
        }

        // check that FrameworkBundle 6.3 or higher is installed
        $bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
        if (!isset($bundlesMetadata['FrameworkBundle'])) {
            return false;
        }

        return is_file($bundlesMetadata['FrameworkBundle']['path'].'/Resources/config/asset_mapper.php');
    }
}
