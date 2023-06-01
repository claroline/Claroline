<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\PrivacyBundle\Entity\Privacy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PrivacyController extends AbstractCrudController
{
    use RequestDecoderTrait;

    private AuthorizationCheckerInterface $authorization;
    private PlatformConfigurationHandler $config;
    private SerializerProvider $privacySerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $ch,
        SerializerProvider $privacySerializer
    ) {
        $this->authorization = $authorization;
        $this->config = $ch;
        $this->privacySerializer = $privacySerializer;
    }

    /**
     * @Route("/privacy")
     */
    public function getName(): string
    {
        return 'privacy';
    }

    public function getClass(): string
    {
       return Privacy::class;
    }

    /**
     * @Route("/privacy/update", name="apiv2_privacy_update", methods={"PUT"})
     */
    public function updatePrivacyStorage(Request $request): JsonResponse
    {
        // todo

        $parameters = $this->decodeRequest($request);
/*
        $parameters = $this->privacySerializer->deserialize($parametersData);
        $this->config->setParameters($parameters);
*/
        return new JsonResponse($parameters);
    }
}
