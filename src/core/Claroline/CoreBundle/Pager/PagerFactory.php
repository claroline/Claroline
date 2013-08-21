<?php

namespace Claroline\CoreBundle\Pager;

use Doctrine\ORM\Query;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Adapter\ArrayAdapter;
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

        return $this->createPagerfanta($adapter, $currentPage);
    }

    public function createPagerFromArray(array $datas, $currentPage)
    {
        $adapter = new ArrayAdapter($datas);

        return $this->createPagerfanta($adapter, $currentPage);
    }

    private function createPagerfanta(AdapterInterface $adapter, $currentPage)
    {
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20); // should be configurable
        $pager->setCurrentPage($currentPage);

        return $pager;
    }
}
