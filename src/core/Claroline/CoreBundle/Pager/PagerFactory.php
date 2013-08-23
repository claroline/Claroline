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
    public function createPager(Query $query, $currentPage, $max = 20)
    {
        $adapter = new DoctrineORMAdapter($query);

        return $this->createPagerfanta($adapter, $currentPage, $max);
    }

    public function createPagerFromArray(array $datas, $currentPage, $max = 20)
    {
        $adapter = new ArrayAdapter($datas);

        return $this->createPagerfanta($adapter, $currentPage, $max);
    }

    private function createPagerfanta(AdapterInterface $adapter, $currentPage, $max = 20)
    {
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage($max); // should be configurable
        $pager->setCurrentPage($currentPage);

        return $pager;
    }
}
