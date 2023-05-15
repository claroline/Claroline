<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\Utils\ArrayUtils;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Exception;
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
      * @Route("/privacy", name="apiv2_privacy_update", methods={"PUT"})
      *
      * @throws InvalidDataException
      * @throws Exception
      */
     public function updateAction(Request $request): JsonResponse
     {
         $this->canOpenAdminTool('privacy');

         $parametersData = $this->decodeRequest($request);

         ArrayUtils::remove($parametersData, 'lockedParameters');

         $locked = $this->config->getParameter('lockedParameters') ?? [];
         foreach ($locked as $lockedParam) {
             ArrayUtils::remove($parametersData, $lockedParam);
         }

         $parameters = $this->serializer->deserialize($parametersData);

         return new JsonResponse($parameters);
     }
 }