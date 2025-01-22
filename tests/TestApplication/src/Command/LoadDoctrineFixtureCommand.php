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

use Doctrine\ORM\EntityManagerInterface;
use Mezcalito\UxSearchBundle\Tests\TestApplication\Entity\Product;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-doctrine-fixture',
    description: 'Load Doctrine fixtures'
)]
class LoadDoctrineFixtureCommand extends Command
{
    public const string DATASET = 'https://raw.githubusercontent.com/algolia/datasets/refs/heads/master/ecommerce/records.json';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $data = json_decode(file_get_contents(self::DATASET), true);

        $i = 0;
        foreach ($data as $item) {
            ++$i;
            $product = (new Product())
                ->setName($item['name'])
                ->setDescription($item['description'])
                ->setType($item['type'])
                ->setBrand($item['brand'])
                ->setPrice((float) $item['price'])
                ->setPriceRange($item['price_range'])
                ->setFreeShipping((bool) $item['free_shipping'])
                ->setPopularity((int) $item['popularity'])
                ->setRating((int) $item['rating']);

            $this->entityManager->persist($product);

            if (0 === $i % 100) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }

        $this->entityManager->flush();

        $io->success('Products imported to database');

        return Command::SUCCESS;
    }
}
