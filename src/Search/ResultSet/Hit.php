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

namespace Mezcalito\UxSearchBundle\Search\ResultSet;

class Hit
{
    public function __construct(
        private array|object $data,
        private float $score,
    ) {
    }

    public function getData(): object|array
    {
        return $this->data;
    }

    public function setData(object|array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): self
    {
        $this->score = $score;

        return $this;
    }
}
