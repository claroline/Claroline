<?php

namespace UJM\ExoBundle\Services\classes;

use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Services for the pagination.
 */
class PaginationService
{
    /**
     * To paginate two tables on one page.
     *
     *
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateOne
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateTwo
     * @param int                                                      $max                 number max items per page
     * @param int                                                      $pageOne             set current page for the first pagination
     * @param int                                                      $pageTwo             set current page for the second pagination
     *
     * @return array
     */
    public function doublePagination($entityToPaginateOne, $entityToPaginateTwo, $max, $pageOne, $pageTwo)
    {
        $adapterOne = new ArrayAdapter($entityToPaginateOne);
        $pagerOne = new Pagerfanta($adapterOne);

        $adapterTwo = new ArrayAdapter($entityToPaginateTwo);
        $pagerTwo = new Pagerfanta($adapterTwo);

        try {
            $entityPaginatedOne = $pagerOne
                ->setMaxPerPage($max)
                ->setCurrentPage($pageOne)
                ->getCurrentPageResults();

            $entityPaginatedTwo = $pagerTwo
                ->setMaxPerPage($max)
                ->setCurrentPage($pageTwo)
                ->getCurrentPageResults();
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $doublePagination[0] = $entityPaginatedOne;
        $doublePagination[1] = $pagerOne;

        $doublePagination[2] = $entityPaginatedTwo;
        $doublePagination[3] = $pagerTwo;

        return $doublePagination;
    }

    /**
     * To paginate table.
     *
     *
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginate
     * @param int                                                      $max              number max items per page
     * @param int                                                      $page             set current page for the pagination
     * @param int                                                      $pageNow          is the current page
     *
     * @return array
     */
    public function paginationWithIf($entityToPaginate, $max, $page, $pageNow)
    {
        $adapter = new ArrayAdapter($entityToPaginate);
        $pager = new Pagerfanta($adapter);

        try {
            if ($pageNow == 0) {
                $entityPaginated = $pager
                    ->setMaxPerPage($max)
                    ->setCurrentPage($page)
                    ->getCurrentPageResults();
            } else {
                $entityPaginated = $pager
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNow)
                    ->getCurrentPageResults();
            }
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $pagination[0] = $entityPaginated;
        $pagination[1] = $pager;

        return $pagination;
    }
    /**
     * To paginate table.
     *
     *
     * @param Doctrine Collection $entityToPaginate
     * @param int                 $max              number max items by page
     * @param int                 $page             number of actual page
     *
     * @return array
     */
    public function pagination($entityToPaginate, $max, $page)
    {
        $adapter = new ArrayAdapter($entityToPaginate);
        $pager = new Pagerfanta($adapter);

        try {
            $entityPaginated = $pager
                ->setMaxPerPage($max)
                ->setCurrentPage($page)
                ->getCurrentPageResults();
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $pagination[0] = $entityPaginated;
        $pagination[1] = $pager;

        return $pagination;
    }
    /**
     * To paginate two tables on one page.
     *
     *
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateOne
     * @param Doctrine Collection of \UJM\ExoBundle\Entity\Interaction $entityToPaginateTwo
     * @param int                                                      $max                 number max items per page
     * @param int                                                      $pageOne             set new page for the first pagination
     * @param int                                                      $pageTwo             set new page for the second pagination
     * @param int                                                      $pageNowOne          set current page for the first pagination
     * @param int                                                      $pageNowTwo          set current page for the second pagination
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function doublePaginationWithIf($entityToPaginateOne, $entityToPaginateTwo, $max, $pageOne, $pageTwo, $pageNowOne, $pageNowTwo)
    {
        $adapterOne = new ArrayAdapter($entityToPaginateOne);
        $pagerOne = new Pagerfanta($adapterOne);

        $adapterTwo = new ArrayAdapter($entityToPaginateTwo);
        $pagerTwo = new Pagerfanta($adapterTwo);

        try {
            if ($pageNowOne == 0) {
                $entityPaginatedOne = $pagerOne
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageOne)
                    ->getCurrentPageResults();
            } else {
                $entityPaginatedOne = $pagerOne
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNowOne)
                    ->getCurrentPageResults();
            }

            if ($pageNowTwo == 0) {
                $entityPaginatedTwo = $pagerTwo
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageTwo)
                    ->getCurrentPageResults();
            } else {
                $entityPaginatedTwo = $pagerTwo
                    ->setMaxPerPage($max)
                    ->setCurrentPage($pageNowTwo)
                    ->getCurrentPageResults();
            }
        } catch (\Pagerfanta\Exception\NotValidCurrentPageException $e) {
            throw $this->createNotFoundException("Cette page n'existe pas.");
        }

        $doublePagination[0] = $entityPaginatedOne;
        $doublePagination[1] = $pagerOne;

        $doublePagination[2] = $entityPaginatedTwo;
        $doublePagination[3] = $pagerTwo;

        return $doublePagination;
    }
}
