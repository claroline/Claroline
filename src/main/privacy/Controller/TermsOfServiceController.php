<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/terms_of_service")
 */
class TermsOfServiceController
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly PrivacyManager $privacyManager
    ) {
    }

    /**
     * @Route("/", name="apiv2_platform_terms_of_service", methods={"GET"})
     */
    public function getCurrentAction(Request $request): JsonResponse
    {
        $tos = null;
        if ($this->privacyManager->getParameters()->getTosEnabled()) {
            $tos = $this->privacyManager->getTosTemplate($request->getLocale());
        }

        return new JsonResponse($tos);
    }

    /**
     * @Route("/accept", name="apiv2_platform_terms_of_service_accept", methods={"PUT"})
     *
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=true})
     */
    public function acceptAction(User $currentUser): JsonResponse
    {
        $currentUser->setAcceptedTerms(true);

        $this->om->persist($currentUser);
        $this->om->flush();

        return new JsonResponse($this->serializer->serialize($currentUser));
    }
}
