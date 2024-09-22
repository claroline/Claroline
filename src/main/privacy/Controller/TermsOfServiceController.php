<?php

namespace Claroline\PrivacyBundle\Controller;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/terms_of_service')]
class TermsOfServiceController
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly SerializerProvider $serializer,
        private readonly PrivacyManager $privacyManager
    ) {
    }

    #[Route(path: '/', name: 'apiv2_platform_terms_of_service', methods: ['GET'])]
    public function getCurrentAction(Request $request): JsonResponse
    {
        $tos = null;
        if ($this->privacyManager->getTosEnabled($request->getLocale())) {
            $tos = $this->privacyManager->getTosTemplate($request->getLocale());
        }

        return new JsonResponse($tos);
    }

    #[Route(path: '/accept', name: 'apiv2_platform_terms_of_service_accept', methods: ['PUT'])]
    public function acceptAction(#[CurrentUser] ?User $currentUser): JsonResponse
    {
        if (null === $currentUser) {
            return new JsonResponse(null, 204);
        }

        $currentUser->setAcceptedTerms(true);

        $this->om->persist($currentUser);
        $this->om->flush();

        return new JsonResponse($this->serializer->serialize($currentUser));
    }
}
