<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
