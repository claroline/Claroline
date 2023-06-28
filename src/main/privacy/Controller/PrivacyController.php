<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\TermsOfServiceManager;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;
use Claroline\PrivacyBundle\Serializer\PrivacySerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private SerializerProvider $serializer;
    private ObjectManager $objectManager;
    private Crud $crud;
    private TokenStorageInterface $tokenStorage;
    private PrivacySerializer $privacySerializer;
    private TermsOfServiceManager $termsOfServiceManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        SerializerProvider $serializer,
        ObjectManager $objectManager,
        Crud $crud,
        TokenStorageInterface $tokenStorage,
        PrivacySerializer $privacySerializer,
        TermsOfServiceManager $termsOfServiceManager
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
        $this->crud = $crud;
        $this->tokenStorage = $tokenStorage;
        $this->privacySerializer = $privacySerializer;
        $this->termsOfServiceManager = $termsOfServiceManager;
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
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('privacy');

        $data = $this->decodeRequest($request);
        $privacyParameters = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);
        $privacyUpdate = $this->crud->update($privacyParameters, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($privacyUpdate)
        );
    }

    /**
     * @Route("", name="apiv2_terms_of_service", methods={"GET"})
     */
    public function getTermsAction(): JsonResponse
    {
        $termsOfService = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);
        $terms = $termsOfService->getTermsOfService();

        return new JsonResponse($terms);
    }
}
