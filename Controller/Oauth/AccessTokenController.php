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

use Claroline\CoreBundle\Entity\Oauth\AccessToken;
use Claroline\CoreBundle\Entity\Oauth\Client;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class AccessTokenController extends Controller
{
    /**
     * @Route("/oauth/revok/{client_id}/{accessToken_id}", name="claro_profile_applications_revoke")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("client", class="ClarolineCoreBundle:Oauth\Client", options={"id" = "client_id"})
     * @ParamConverter("accessToken", class="ClarolineCoreBundle:Oauth\AccessToken", options={"id" = "accessToken_id"})
     *
     * @Template()
     */
    public function revokAction(Request $request, User $user, Client $client, AccessToken $accessToken)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();

            if ($client !== $accessToken->getClient()) {
                throw new \InvalidArgumentException("Access token client doesn't match provided client.");
            }
            if ($user !== $accessToken->getUser()) {
                throw new \InvalidArgumentException("Access token user doesn't match connected user.");
            }

            $entityManager->remove($accessToken);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('application_revoked_success_message', array('%application%' => $client->getName()), 'api'));
        } catch (\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('application_revoked_error_message', array('%application%' => $client->getName()), 'api'));
        }

        return $this->redirect($this->generateUrl('claro_profile_applications'));
    }
}
 