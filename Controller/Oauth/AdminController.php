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

use Claroline\CoreBundle\Entity\Oauth\Client;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

    /**
     * @Route("/applications/delete/{client_id}", name="admin_application_delete")
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

            $entityManager->remove($client);
            $entityManager->flush();

            $this->get('session')->getFlashBag()->add('success', $translator->trans('thrid_party_application_delete_success_message', array('%application%' => $client->getName()), 'api'));
        } catch (\Exception $exception) {
            $this->get('session')->getFlashBag()->add('error', $translator->trans('thrid_party_application_delete_error_message', array('%application%' => $client->getName()), 'api'));
        }

        return $this->redirect($this->generateUrl('admin_application_list'));
    }
}
 