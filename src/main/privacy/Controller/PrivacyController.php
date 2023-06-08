<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\PrivacyBundle\Entity\Privacy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private SerializerProvider $serializer;
    private ObjectManager $objectManager;
    private Crud $crud;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        SerializerProvider $serializer,
        ObjectManager $objectManager,
        Crud $crud
    ) {
        $this->authorization = $authorization;
        $this->serializer = $serializer;
        $this->objectManager = $objectManager;
        $this->crud = $crud;
    }

    public function getName(): string
    {
        return 'privacy';
    }

    public function getClass(): string
    {
        return Privacy::class;
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
        $privacyEntity = $this->objectManager->getRepository(Privacy::class)->findOneBy([], ['id' => 'ASC']);
        $privacyUpdate = $this->crud->update($privacyEntity, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($privacyUpdate)
        );
    }
}
