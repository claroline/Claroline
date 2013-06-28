<?php

namespace Claroline\CoreBundle\Pager;

use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.pager.pager_factory")
 */
class PagerFactory
{
    public function createPager(Query $query, $currentPage)
    {
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20); // should be configurable
        $pager->setCurrentPage($currentPage);

        return $pager;
    }
}