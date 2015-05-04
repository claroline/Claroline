<?php
/**
 * This file is part of the Claroline Connect package
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 * 
 * Date: 4/22/15
 */

namespace Icap\SocialmediaBundle\Controller;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    const MAX_PER_PAGE = 1;
    /**
     * @return \Icap\SocialmediaBundle\Manager\LikeActionManager
     */
    public function getLikeActionManager()
    {
        return $this->get("icap_socialmedia.manager.like_action");
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\ShareActionManager
     */
    public function getShareActionManager()
    {
        return $this->get("icap_socialmedia.manager.share_action");
    }

    public function paginateQuery($queryBuilder, $page)
    {
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);

        $pagerfanta->setMaxPerPage(self::MAX_PER_PAGE);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
} 