<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\PrivacyBundle\Entity\Privacy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PrivacyController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private PlatformConfigurationHandler $config;
    private SerializerProvider $privacySerializer;
    private ObjectManager $objectManager;
    private Crud $crud;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $ch,
        SerializerProvider $privacySerializer,
        ObjectManager $objectManager,
        Crud $crud
    ) {
        $this->authorization = $authorization;
        $this->config = $ch;
        $this->privacySerializer = $privacySerializer;
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
     */
    public function updateAction(Request $request): JsonResponse
    {
        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $data = $this->decodeRequest($request);
        $privacyRepository = $this->objectManager->getRepository(Privacy::class)->findOneBy([], ['id' => 'ASC']);
        $privacyUpdate = $this->crud->update($privacyRepository, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->privacySerializer->serialize($privacyUpdate)
        );
    }
}
