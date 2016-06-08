<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 6/6/16
 */

namespace Claroline\CoreBundle\Library\Utilities;

use Pagerfanta\Pagerfanta;

class PaginatedCollectionRepresentation
{
    /** @var Pagerfanta  */
    private $pager;

    public function createRepresentationFromPagerfanta(Pagerfanta $pager)
    {
        $this->pager = empty($pager) ? $this->pager : $pager;
        if (empty($this->pager)) {
            return array();
        }
        $representation = array(
            'hasToPaginate' => $this->pager->haveToPaginate(),
            'hasNextPage' => $this->pager->hasNextPage(),
            'hasPreviousPage' => $this->pager->hasPreviousPage(),
            'totalItems' => $this->pager->getNbResults(),
            'itemsPerPage' => $this->pager->getMaxPerPage(),
            'currentPage' => $this->pager->getCurrentPage(),
            'data' => $this->pager->getCurrentPageResults(),
        );

        return $representation;
    }

    public function createRepresentationFromValues($data, $totalItems, $itemsPerPage, $currentPage)
    {
        $representation = array(
            'hasToPaginate' => $totalItems > $itemsPerPage,
            'hasNextPage' => $currentPage < (int) ceil($totalItems / $itemsPerPage),
            'hasPreviousPage' => $currentPage > 1,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'currentPage' => $currentPage,
            'data' => $data,
        );

        return $representation;
    }
}
