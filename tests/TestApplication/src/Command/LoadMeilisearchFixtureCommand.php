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

namespace Mezcalito\UxSearchBundle\Tests\TestApplication\Command;

use Mezcalito\UxSearchBundle\Adapter\Meilisearch\MeilisearchFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-meilisearch-fixture',
    description: 'Load Meilisearch fixtures'
)]
class LoadMeilisearchFixtureCommand extends Command
{
    public const string DATASET = 'https://raw.githubusercontent.com/algolia/datasets/refs/heads/master/ecommerce/records.json';
    public const string DSN = 'meilisearch://secret@uxsearch_meilisearch:7700?tls=false';

    public function __construct(
        private readonly MeilisearchFactory $clientFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = $this->clientFactory->createClient(self::DSN);
        $client->createIndex('products', ['primaryKey' => 'objectID']);

        $index = $client->getIndex('products');
        $index->updateSettings([
            'filterableAttributes' => [
                'type',
                'price',
                'brand',
                'rating',
                'free_shipping',
                'price_range',
            ],
            'sortableAttributes' => [
                'price',
                'popularity',
            ],
            'pagination' => [
                'maxTotalHits' => 10000,
            ],
            'faceting' => [
                'maxValuesPerFacet' => 200,
                'sortFacetValuesBy' => [
                    '*' => 'count',
                    'rating' => 'alpha',
                ],
            ],
        ]);

        $data = json_decode(file_get_contents(self::DATASET), true);

        $index->addDocuments($data, 'objectID');

        $io->success('Products imported to Meilisearch');

        return Command::SUCCESS;
    }
}
