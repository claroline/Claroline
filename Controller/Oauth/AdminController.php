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
use JMS\SecurityExtraBundle\Annotation as SEC;

/**
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 * @Route("/admin")
 */
class AdminController extends Controller
{
    /**
     * @Route("/applications", name="admin_application_list")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @Template()
     */
    public function listAction(Request $request, User $user)
    {
        $clients = $this->getDoctrine()->getRepository('ClarolineCoreBundle:Oauth\Client')->findAll();

        return array(
            'clients' => $clients
        );
    }
}
 