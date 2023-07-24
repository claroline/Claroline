<?php

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AuthenticationBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
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

    /**
     * @Route("/authentication", name="apiv2_authentication_parameters_update", methods={"PUT"})
     *
     * @throws InvalidDataException
     * @throws \Exception
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('main_settings');

        $data = $this->decodeRequest($request);
        $passwordData = $data['password'] ?? [];
        $loginData = $data['login'] ?? [];
        $authenticationParameters = $this->authenticationManager->getParameters();
        $authenticationParametersUpdate = $this->crud->update($authenticationParameters, ['password' => $passwordData, 'login' => $loginData], [Crud::THROW_EXCEPTION]);

        $serializedParameters = $this->serializer->serialize($authenticationParametersUpdate);

        return new JsonResponse([
            'password' => $serializedParameters['password'],
            'login' => $serializedParameters['login'],
        ]);
    }
}
