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
    /**
     * @param int  $page
     * @param int  $limit
     * @param bool $count
     *
     * @return array
     */
    public function find(array $filters = [], array $sortBy = null, $page = 0, $limit = -1, $count = false);

    /**
     * @return object
     */
    public function findOneBy(array $filters = []);

    public static function getClass(): string;

    /**
     * Allow us to make optimize sql directly by mapping serialized property path to their own database colum.
     *
     * @return array
     */
    public function getExtraFieldMapping();
}
