<?php

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthenticationParametersController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private Crud $crud;
    private ObjectManager $objectManager;
    private SerializerProvider $serializer;
    private AuthorizationCheckerInterface $authorization;

    public function __construct(
        Crud $crud,
        ObjectManager $objectManager,
        SerializerProvider $serializer,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->crud = $crud;
        $this->objectManager = $objectManager;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
    }

    /**
     * @Route("/authentication", name="apiv2_authentication_update", methods={"PUT"})
     * @throws InvalidDataException
     * @throws \Exception
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('main_settings');

        $data = $this->decodeRequest($request);
        $authenticationParameters = $this->objectManager->getRepository(AuthenticationParameters::class)->findOneBy([]);
        $authenticationParametersUpdate = $this->crud->update($authenticationParameters, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($authenticationParametersUpdate)
        );
    }
}
