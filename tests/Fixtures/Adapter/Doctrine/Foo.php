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

namespace Mezcalito\UxSearchBundle\Tests\Fixtures\Adapter\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Foo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    public function __construct(
        #[ORM\Column] public ?string $type = null,
        #[ORM\Column] public ?string $brand = null,
        #[ORM\Column] public ?float $price = null,
    ) {
    }
}
