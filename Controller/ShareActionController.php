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


use Claroline\CoreBundle\Entity\User;
use Icap\SocialmediaBundle\Entity\ShareAction;
use Symfony\Component\HttpFoundation\Request;

class ShareActionController extends Controller
{
    /**
     * @Route("/share.{_format}", name="icap_socialmedia_share", defaults={"_format" = "json"})
     * @param Request $request
     * @param User $user
     * @return bool
     */
    public function shareAction(Request $request, User $user)
    {
        $share = new ShareAction();
        $share->setUser($user);
        $this->getShareActionManager()->createShare($request, $share);

        return true;
    }
} 