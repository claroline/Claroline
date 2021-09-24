<?php

/*
 * This file is part of the LightSAML SP-Bundle package.
 *
 * (c) Milos Tomic <tmilos@lightsaml.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Claroline\SamlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AuthenticationController extends AbstractController
{
    public function loginAction(Request $request)
    {
        if (!empty($request->get('redirectPath')) && '#/login' !== $request->get('redirectPath')) {
            // store it in session before leaving claroline for authentication
            // this will allow use to redirect to the correct ui fragment when going back to claroline
            $request->getSession()->set('redirectPath', $request->get('redirectPath'));
        }

        $idpEntityId = $request->get('idp');
        if (null === $idpEntityId) {
            return $this->redirect($this->generateUrl($this->container->getParameter('lightsaml_sp.route.discovery')));
        }

        // we store idp in session to retrieve it at the end of authentication success
        // in order to register user to correct groups and organization based on the idp configuration
        $request->getSession()->set('authIdp', $idpEntityId);

        $profile = $this->get('ligthsaml.profile.login_factory')->get($idpEntityId);
        $context = $profile->buildContext();
        $action = $profile->buildAction();

        $action->execute($context);

        return $context->getHttpResponseContext()->getResponse();
    }
}
