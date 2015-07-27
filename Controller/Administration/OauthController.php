<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Manager\OauthManager;
use Claroline\CoreBundle\Entity\Oauth\Client;
use Claroline\CoreBundle\Form\Administration\OauthClientType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
 */
class OauthController extends Controller
{
    private $oauthManager;
    private $request;

    /**
     * @DI\InjectParams({
     *     "oauthManager" = @DI\Inject("claroline.manager.oauth_manager"),
     *     "request"      = @DI\Inject("request")
     * })
     */
    public function __construct(
        OauthManager $oauthManager,
        $request
    ) {
        $this->oauthManager = $oauthManager;
        $this->request = $request;
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_oauth_claroline",
     *     options = {"expose"=true}
     * )
     * @EXT\Template()
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function listAction()
    {
        $clients = $this->oauthManager->findAllClients();

        return array('clients' => $clients);
    }

    /**
     * @EXT\Route(
     *     "/form",
     *     name="claro_admin_oauth_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template()
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function modalCreateFormAction()
    {
        $form = $this->get('form.factory')->create(new OauthClientType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/form/create",
     *     name="claro_admin_oauth_client_create",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:oauth\clientModalForm.html.twig")
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function createClientAction()
    {
        $form = $this->get('form.factory')->create(new OauthClientType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $grantTypes = $form->get('allowed_grant_types')->getData();
            $client = $this->oauthManager->createClient();
            if ($uri = $form->get('uri')->getData()) $client->setRedirectUris(array($uri));
            $client->setAllowedGrantTypes($grantTypes);
            $client->setName($form->get('name')->getData());
            $this->oauthManager->updateClient($client);

            return new JsonResponse(array(
                'id' => $client->getId(),
                'name' => $form->get('name')->getData(),
                'uri' => $form->get('uri')->getData(),
                'grant_type' => $grantTypes
            ));
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/delete/client/{client}",
     *     name="oauth_client_remove",
     *     options = {"expose"=true}
     * )
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function deleteClientAction(Client $client)
    {
        $oldid = $client->getId();
        $this->oauthManager->deleteClient($client);

        return new JsonResponse(array('id' => $oldid));

    }
}
