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

namespace Mezcalito\UxSearchBundle\Search\Url;

use Mezcalito\UxSearchBundle\Search\Query;
use Mezcalito\UxSearchBundle\Search\SearchInterface;

interface UrlFormaterInterface
{
    public function generateUrl(CurrentRequest $currentRequest, SearchInterface $search, Query $query): string;

    public function applyFilters(CurrentRequest $currentRequest, SearchInterface $search, Query $query): void;
}
