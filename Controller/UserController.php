<?php

namespace Claroline\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;

class UserController extends Controller
{

    /**
     * @EXT\Template("ClarolineCoreBundle:User:popover.html.twig")
     */
    public function renderPopoverAction(User $user)
    {
        return array('user' => $user);
    }
}