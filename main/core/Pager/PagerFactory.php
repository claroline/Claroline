<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Pager;

use Doctrine\ORM\Query;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.pager.pager_factory")
 */
class PagerFactory
{
    public function createPager(Query $query, $currentPage, $max = 20)
    {
        $adapter = new DoctrineORMAdapter($query);

        return $this->createPagerWithAdapter($adapter, $currentPage, $max);
    }

    public function createPagerFromArray(array $datas, $currentPage, $max = 20)
    {
        $adapter = new ArrayAdapter($datas);

        return $this->createPagerWithAdapter($adapter, $currentPage, $max);
    }

    public function createPagerWithAdapter(AdapterInterface $adapter, $currentPage, $max = 20)
    {
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($max); // should be configurable

        try {
            $pager->setCurrentPage($currentPage);
        } catch (OutOfRangeCurrentPageException $e) {
            $pager->setCurrentPage(1);
        }

        return $pager;
    }
}
