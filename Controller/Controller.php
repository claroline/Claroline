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

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @return \Icap\SocialmediaBundle\Manager\LikeActionManager
     */
    public function getLikeActionManager()
    {
        return $this->get("icap.social_media.manager.like_action");
    }

    /**
     * @return \Icap\SocialmediaBundle\Manager\ShareActionManager
     */
    public function getShareActionManager()
    {
        return $this->get("icap.social_media.manager.share_action");
    }
} 