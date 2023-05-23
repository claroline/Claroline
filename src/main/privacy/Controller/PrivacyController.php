<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PrivacyController extends AbstractSecurityController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private PlatformConfigurationHandler $config;
    private ParametersSerializer $serializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $ch,
        ParametersSerializer $serializer
    ) {
        $this->authorization = $authorization;
        $this->config = $ch;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/privacy", name="apiv2_privacy_country_storage_update", methods={"PUT"})
     */
    public function updateCountryStorage(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('privacy');

        $parametersData = $this->decodeRequest($request);

        $parameters = $this->serializer->deserialize($parametersData);
        $this->config->setParameters($parameters);

        return new JsonResponse($parameters);
    }

    /**
     * @Route("/privacy", name="apiv2_privacy_dpo_update", methods={"PUT"})
     */
    public function updateInfosDpo(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('privacy');

        $parametersData = $this->decodeRequest($request);

        $parameters = $this->serializer->deserialize($parametersData);
        $this->config->setParameters($parameters);

        return new JsonResponse($parameters);
    }

    /**
     * @Route("/privacy", name="apiv2_privacy_therms_update", methods={"PUT"})
     */
    public function updateThermsOfService(Request $request): JsonResponse
    {
        $this->canOpenAdminTool('privacy');

        $parametersData = $this->decodeRequest($request);

        $parameters = $this->serializer->deserialize($parametersData);
        $this->config->setParameters($parameters);

        return new JsonResponse($parameters);
    }
}
