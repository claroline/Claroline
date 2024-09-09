<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle\API\Finder;

interface FinderInterface
{
    public function find(?array $filters = [], array $sortBy = null, ?int $page = 0, ?int $limit = -1, ?bool $count = false): array|int;

    public static function getClass(): string;

    public function search(FinderQuery $query): FinderResult;
}
