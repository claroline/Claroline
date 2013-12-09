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

use Claroline\CoreBundle\Entity\Oauth\AuthCode;
use Claroline\CoreBundle\Entity\Oauth\Client;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

class AccessController extends Controller
{
    /**
     * @Route("/oauth/revok/{client_id}", name="claro_profile_applications_revoke")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @ParamConverter("client", class="ClarolineCoreBundle:Oauth\Client", options={"id" = "client_id"})
     *
     * @Template()
     */
    public function revokAction(Request $request, User $user, Client $client)
    {
        /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator $translator */
        $translator = $this->get('translator');
        try {
            /** @var \Doctrine\Common\Persistence\ObjectManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();

            $criteria = array('client' => $client, 'user' => $user);

            $accessToken = $entityManager->getRepository('ClarolineCoreBundle:Oauth\AccessToken')->findOneBy($criteria);
            $entityManager->remove($accessToken);

            $refreshToken = $entityManager->getRepository('ClarolineCoreBundle:Oauth\RefreshToken')->findOneBy($criteria);
            if (null !== $refreshToken) {
                $entityManager->remove($refreshToken);
            }

            $authCodes = $entityManager->getRepository('ClarolineCoreBundle:Oauth\AuthCode')->findBy($criteria);
            foreach ($authCodes as $authCode) {
                $entityManager->remove($authCode);
            }

            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('application_revoked_success_message', array('%application%' => $client->getName()), 'api'));
        } catch (\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('application_revoked_error_message', array('%application%' => $client->getName()), 'api'));
        }

        return $this->redirect($this->generateUrl('claro_profile_applications'));
    }
}
 