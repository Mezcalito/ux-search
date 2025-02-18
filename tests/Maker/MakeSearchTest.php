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

namespace Mezcalito\UxSearchBundle\Tests\Maker;

use Mezcalito\UxSearchBundle\Maker\MakeSearch;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

class MakeSearchTest extends KernelTestCase
{
    private const string COMMAND_NAME = 'make:search';

    private Filesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filesystem = new Filesystem();
    }

    public function testGetCommandName(): void
    {
        $commandName = MakeSearch::getCommandName();

        $this->assertEquals(self::COMMAND_NAME, $commandName);
    }

    public function testGetCommandDescription(): void
    {
        $commandDescription = MakeSearch::getCommandDescription();

        $this->assertEquals('Create a new search class', $commandDescription);
    }

    public function testGenerateSearchClassWithIndex(): void
    {
        $this->executeCommandAndAssertFile('ListingSearch', 'meilisearch_index');
    }

    public function testGenerateSearchClassWithDoctrineEntity(): void
    {
        $this->executeCommandAndAssertFile('NewsSearch', 'App\Entity\Post::class');
    }

    private function executeCommandAndAssertFile(string $name, string $indexName): void
    {
        self::bootKernel();

        $command = self::getContainer()->get('console.command_loader')->get(self::COMMAND_NAME);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'name' => $name,
            'indexName' => $indexName,
        ]);

        $generatedFile = $this->getProjectDir().'/src/Search/'.$name.'.php';
        $this->assertFileExists($generatedFile);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Next: open your new search class and customize it!', $output);

        $this->filesystem->remove($generatedFile);
    }

    public function testAddUseStatement(): void
    {
        $makeSearch = new MakeSearch();

        $makeSearch->addUseStatement('App\\Entity\\Post');
        $makeSearch->addUseStatement('App\\Entity\\User');
        $makeSearch->addUseStatement('App\\Entity\\Post'); // Duplication

        $reflection = new \ReflectionClass($makeSearch);
        $property = $reflection->getProperty('classesToBeImported');

        $classesToBeImported = $property->getValue($makeSearch);

        $this->assertContains('App\\Entity\\Post', $classesToBeImported);
        $this->assertContains('App\\Entity\\User', $classesToBeImported);
        $this->assertCount(2, $classesToBeImported);
    }

    private function getProjectDir(): string
    {
        return self::getContainer()->getParameter('kernel.project_dir');
    }
}
