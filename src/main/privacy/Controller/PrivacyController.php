<?php

namespace Claroline\PrivacyBundle\Controller;

use Exception;
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

    public function __construct(
        private readonly Crud $crud,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly PlatformConfigurationHandler $config,
        private readonly PrivacyParametersSerializer $serializer,
        private readonly PrivacyManager $manager
    ) {
    }

    /**
     *
     * @throws InvalidDataException
     * @throws Exception
     */
    #[Route(path: '/privacy', name: 'apiv2_privacy_update', methods: ['PUT'])]
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
