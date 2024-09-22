<?php

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AuthenticationParametersController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private Crud $crud;
    private AuthenticationManager $authenticationManager;
    private SerializerProvider $serializer;
    private AuthorizationCheckerInterface $authorization;

    public function __construct(
        Crud $crud,
        AuthenticationManager $authenticationManager,
        SerializerProvider $serializer,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->crud = $crud;
        $this->authenticationManager = $authenticationManager;
        $this->serializer = $serializer;
        $this->authorization = $authorization;
    }

    #[Route(path: '/authentication', name: 'apiv2_authentication_parameters_update', methods: ['PUT'])]
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('authentication');

        $data = $this->decodeRequest($request);
        $authenticationParameters = $this->authenticationManager->getParameters();
        $authenticationParametersUpdate = $this->crud->update($authenticationParameters, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($authenticationParametersUpdate)
        );
    }
}
