<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Claroline\PrivacyBundle\Serializer\PrivacySerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private SerializerProvider $serializer;
    private ObjectManager $objectManager;
    private Crud $crud;
    private TokenStorageInterface $tokenStorage;
    private PrivacyManager $privacyManager;
    private PrivacySerializer $privacySerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        SerializerProvider $serializer,
        ObjectManager $objectManager,
        Crud $crud,
        TokenStorageInterface $tokenStorage,
        PrivacyManager $privacyManager,
        PrivacySerializer $privacySerializer
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
        $this->crud = $crud;
        $this->tokenStorage = $tokenStorage;
        $this->privacyManager = $privacyManager;
        $this->privacySerializer = $privacySerializer;
    }

    public function getName(): string
    {
        return 'privacy';
    }

    public function getClass(): string
    {
        return PrivacyParameters::class;
    }

    /**
     * @Route("/privacy", name="apiv2_privacy_update", methods={"PUT"})
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('privacy');

        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $data = $this->decodeRequest($request);
        $privacyParameters = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);
        $privacyUpdate = $this->crud->update($privacyParameters, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($privacyUpdate)
        );
    }

    /**
     * @Route("/request-deletion", name="apiv2_user_request_account_deletion", methods={"POST"})
     */
    public function requestAccountDeletionAction(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $this->privacyManager->sendRequestToDPO($user);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("", name="apiv2_add_privacy", methods={"GET"})
     *
     * @throws \Exception
     */
    public function getAction(): JsonResponse
    {
        $privacy = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);

        $privacyGet = $this->crud->get(PrivacyParameters::class, $privacy);

        return new JsonResponse([

            'privacyData' => $this->serializer->serialize($privacyGet)
        ]);
    }
}
