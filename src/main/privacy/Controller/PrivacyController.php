<?php

namespace Claroline\PrivacyBundle\Controller;

     use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
     use Claroline\AppBundle\API\Utils\ArrayUtils;
     use Claroline\AppBundle\Controller\AbstractSecurityController;
     use Claroline\AppBundle\Controller\RequestDecoderTrait;
     use Claroline\AppBundle\Event\StrictDispatcher;
     use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
     use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
     use Claroline\CoreBundle\Manager\VersionManager;
     use Symfony\Component\HttpFoundation\JsonResponse;
     use Symfony\Component\HttpFoundation\Request;
     use Symfony\Component\Routing\Annotation\Route;
     use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

     class PrivacyController extends AbstractSecurityController
     {
         use RequestDecoderTrait;

         /** @var AuthorizationCheckerInterface */
         private $authorization;
         /** @var StrictDispatcher */
         private $dispatcher;
         /** @var PlatformConfigurationHandler */
         private $config;
         /** @var AnalyticsManager */
         private $analyticsManager;
         /** @var VersionManager */
         private $versionManager;
         /** @var ParametersSerializer */
         private $serializer;

         public function __construct(
             AuthorizationCheckerInterface $authorization,
             StrictDispatcher $dispatcher,
             PlatformConfigurationHandler $ch,
             AnalyticsManager $analyticsManager,
             VersionManager $versionManager,
             ParametersSerializer $serializer
         ) {
             $this->authorization = $authorization;
             $this->dispatcher = $dispatcher;
             $this->config = $ch;
             $this->serializer = $serializer;
             $this->versionManager = $versionManager;
             $this->analyticsManager = $analyticsManager;
         }

         /**
          * @Route("/privacy", name="apiv2_privacy_update", methods={"PUT"})
          */
         public function updateAction(Request $request): JsonResponse
         {
             $this->canOpenAdminTool('privacy');

             $parametersData = $this->decodeRequest($request);

             ArrayUtils::remove($parametersData, 'lockedParameters');
             // removes locked parameters values if any
             $locked = $this->config->getParameter('lockedParameters') ?? [];
             foreach ($locked as $lockedParam) {
                 ArrayUtils::remove($parametersData, $lockedParam);
             }

             // save updated parameters
             $parameters = $this->serializer->deserialize($parametersData);

             return new JsonResponse($parameters);
         }
     }