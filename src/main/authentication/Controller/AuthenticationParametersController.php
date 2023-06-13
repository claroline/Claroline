<?php

namespace Claroline\AuthenticationBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\AuthenticationParameters;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    public function getName(): string
    {
        return 'authentication_parameters';
    }

    public function getClass(): string
    {
        return AuthenticationParameters::class;
    }

    /**
     * @Route("/authentication_parameters", name="apiv2_authentication_parameters_update", methods={"PUT"})
     * @throws \Exception
     */
    public function updateAction(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('authentication_parameters');

        if (!$this->authorization->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw new AccessDeniedException();
        }

        $data = $this->decodeRequest($request);
        $authenticationParameters = $this->objectManager->getRepository(AuthenticationParameters::class)->findOneBy([], ['id' => 'ASC']);
        $authenticationParametersUpdate = $this->crud->update($authenticationParameters, $data, [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($authenticationParametersUpdate)
        );
    }
}
