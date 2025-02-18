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

namespace Mezcalito\UxSearchBundle\Maker;

use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Search\AbstractSearch;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

class MakeSearch extends AbstractMaker
{
    private array $classesToBeImported = [];

    public static function getCommandName(): string
    {
        return 'make:search';
    }

    public static function getCommandDescription(): string
    {
        return 'Create a new search class';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('name', InputArgument::OPTIONAL, \sprintf('Choose a search name (e.g. <fg=yellow>%sSearch</>)', Str::asClassName(Str::getRandomTerm())))
            ->addArgument('indexName', InputArgument::OPTIONAL, 'Define your index name or Doctrine entity FQCN (Post::class)')
        ;
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): int
    {
        $searchName = trim((string) $input->getArgument('name'));
        $searchNameHasSearchSuffix = str_ends_with($searchName, 'Search');

        $indexName = trim((string) $input->getArgument('indexName'));
        $indexNameIsClassName = str_ends_with($indexName, '::class');

        $searchClassNameDetails = $generator->createClassNameDetails(
            $searchNameHasSearchSuffix ? substr($searchName, 0, -6) : $searchName,
            'Search\\',
            'Search'
        );

        $this->classesToBeImported = [
            AsSearch::class,
            AbstractSearch::class,
        ];

        if ($indexNameIsClassName) {
            $boundClassDetails = $generator->createClassNameDetails(
                substr($indexName, 0, -7),
                'Entity\\'
            );

            $this->addUseStatement($boundClassDetails->getFullName());
        }

        $generator->generateClass(
            $searchClassNameDetails->getFullName(),
            __DIR__.'/../../templates/skeleton/search.tpl.php',
            [
                'use_statements' => $this->generateUse(),
                'search_name' => $searchClassNameDetails->getShortName(),
                'index_name' => $indexNameIsClassName ? \sprintf('%s::class', $boundClassDetails->getShortName()) : \sprintf("'%s'", $indexName),
            ]
        );

        $generator->writeChanges();

        $this->writeSuccessMessage($io);
        $io->text([
            'Next: open your new search class and customize it!',
            'Find the documentation at <fg=yellow>https://github.com/Mezcalito/ux-search/blob/0.x/docs/usage/customize-your-search.md</>',
        ]);

        return Command::SUCCESS;
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    // Override based on UseStatementGenerator::class because is @internal
    public function generateUse(): string
    {
        $transformed = [];
        foreach ($this->classesToBeImported as $key => $class) {
            $transformedClass = str_replace('\\', ' ', $class);
            if (!\in_array($transformedClass, $transformed, true)) {
                $transformed[$key] = $transformedClass;
            }
        }

        asort($transformed);

        $statements = '';

        foreach (array_keys($transformed) as $key) {
            $importedClass = $this->classesToBeImported[$key];

            $statements .= \sprintf("use %s;\n", $importedClass);
        }

        return $statements;
    }

    public function addUseStatement(string $className): void
    {
        if (\in_array($className, $this->classesToBeImported, true)) {
            return;
        }

        $this->classesToBeImported[] = $className;
    }
}
