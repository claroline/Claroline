<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\PrivacyBundle\Entity\PrivacyParameters;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private SerializerProvider $serializer;
    private ObjectManager $objectManager;
    private Crud $crud;

    public function __construct(
        SerializerProvider $serializer,
        ObjectManager $objectManager,
        Crud $crud
    ) {
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
        $this->crud = $crud;
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

        $privacyData = $this->serializer->serialize($privacyUpdate);

        return new JsonResponse($privacyData);
    }

    /**
     * @Route("/privacy", name="apiv2_privacy_dpo_get", methods={"GET"})
     */
    public function getCurrentAction(): JsonResponse
    {
        $firstPrivacy = $this->objectManager->getRepository(PrivacyParameters::class)->findOneBy([], ['id' => 'ASC']);
        $data = $this->serializer->serialize($firstPrivacy);

        return new JsonResponse(['privacyParameters' => $data]);
    }
}
