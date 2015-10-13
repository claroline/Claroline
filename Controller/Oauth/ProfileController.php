<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Oauth;

use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class ProfileController extends Controller
{
    /**
     * @Route("/profile/applications", name="claro_profile_applications")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @Template()
     */
    public function listAction(User $user)
    {
        //???????
        $clients = $this->getDoctrine()
            ->getRepository('ClarolineCoreBundle:Oauth\Client')
            ->findByUserWithAccessToken($user);

        return array(
            'clients' => $clients
        );
    }
}
