<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Claroline\PrivacyBundle\Serializer\PrivacyParametersSerializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private Crud $crud;
    private AuthorizationCheckerInterface $authorization;
    private PlatformConfigurationHandler $config;
    private PrivacyParametersSerializer $serializer;
    private PrivacyManager $manager;

    public function __construct(
        Crud $crud,
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $ch,
        PrivacyParametersSerializer $serializer,
        PrivacyManager $manager
    ) {
        $this->crud = $crud;
        $this->authorization = $authorization;
        $this->config = $ch;
        $this->serializer = $serializer;
        $this->manager = $manager;
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

        $data = $this->decodeRequest($request);

        $privacyParameters = $this->manager->getParameters();

        $updatedPrivacyParameters = $this->serializer->deserialize($data, $privacyParameters);

        $this->manager->updateParameters($updatedPrivacyParameters);

        return new JsonResponse(
            $this->serializer->serialize($updatedPrivacyParameters)
        );
    }
}
