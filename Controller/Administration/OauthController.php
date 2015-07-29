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
use Claroline\CoreBundle\Entity\Oauth\FriendRequest;
use Claroline\CoreBundle\Entity\Oauth\PendingFriend;
use Claroline\CoreBundle\Form\Administration\OauthClientType;
use Claroline\CoreBundle\Form\Administration\RequestFriendType;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Tag("security.secure_service")
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
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function listAction()
    {
        $clients = $this->oauthManager->findAllClients();
        $friendRequests = $this->oauthManager->findAllFriendRequests();
        $pendingFriends = $this->oauthManager->findAllPendingFriends();

        return array(
            'clients' => $clients,
            'friendRequests' => $friendRequests,
            'pendingFriends' => $pendingFriends
        );
    }

    /**
     * @EXT\Route(
     *     "/form",
     *     name="claro_admin_oauth_form",
     *     options = {"expose"=true}
     * )
     * @EXT\Template()
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
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
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
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
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function deleteClientAction(Client $client)
    {
        $oldid = $client->getId();
        $this->oauthManager->deleteClient($client);

        return new JsonResponse(array('id' => $oldid));
    }

    /**
     * @EXT\Route(
     *     "/request/friend/form",
     *     name="oauth_request_friend_form",
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Template()
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function requestFriendFormAction()
    {
        $form = $this->get('form.factory')->create(new RequestFriendType());

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/request/friend/submit",
     *     name="oauth_request_friend_submit",
     *     options = {"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration:oauth\requestFriendForm.html.twig")
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function requestFriendSubmitAction()
    {
        $form = $this->get('form.factory')->create(new RequestFriendType(), new FriendRequest());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $request = $form->getData();
            $data = $this->oauthManager->createFriendRequest($request);

            return new JsonResponse(array(
                'id' => $request->getId(),
                'name' => $request->getName(),
                'host' => $request->getHost(),
                'success' => is_array($data),
                'data' => $data
            ));
        }
    }

    /**
     * @EXT\Route(
     *     "/request/friend/remove/{friend}",
     *     name="oauth_request_friend_remove",
     *     options = {"expose"=true}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function removeFriendRequest(FriendRequest $friend)
    {
        $oldid = $friend->getId();
        $this->oauthManager->removeFriendRequest($friend);

        return new JsonResponse(array('id' => $oldid));
    }

    /**
     * @EXT\Route(
     *     "/request/pending/remove/{friend}",
     *     name="oauth_pending_friend_remove",
     *     options = {"expose"=true}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function removePendingFriend(PendingFriend $friend)
    {
        $oldid = $friend->getId();
        $this->oauthManager->removePendingFriend($friend);

        return new JsonResponse(array('id' => $oldid));
    }

    /**
     * @EXT\Route(
     *     "/request/friend/name/{name}",
     *     name="oauth_request_friend_new",
     *     options = {"expose"=true}
     * )
     *
     * @return Response
     */
    public function newFriendRequestAction($name)
    {
        $host = $this->request->query->get('host');
        $this->oauthManager->addPendingFriendRequest($name, $host);

        return new JsonResponse(array('name' => $name, 'host' => $host));
    }

    /**
     * @EXT\Route(
     *     "/friends/accept/{friend}",
     *     name="oauth_friends_accept",
     *     options = {"expose"=true}
     * )
     * @SEC\PreAuthorize("canOpenAdminTool('platform_parameters')")
     *
     * @return Response
     */
    public function acceptFriendAction(PendingFriend $friend)
    {
        $client = $this->oauthManager->acceptFriendAction($friend);

        return new JsonResponse(array('id' => $client->getId()));
    }

    /**
     * @EXT\Route(
     *     "/id/{id}/secret/{secret}/name/{name}",
     *     name="oauth_receive_data",
     *     options = {"expose"=true}
     * )
     *
     * @return Response
     */
    public function receiveOauthDataAction($id, $secret, $name)
    {
        $friendRequest = $this->oauthManager->findFriendRequestByName($name);
        $access = $this->oauthManager->connect($friendRequest->getHost(), $id, $secret, $friendRequest);

        return new JsonResponse('done');
    }
}
