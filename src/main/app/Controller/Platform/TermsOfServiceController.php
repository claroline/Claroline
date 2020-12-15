<?php

namespace Claroline\AppBundle\Controller\Platform;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\TermsOfServiceManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/terms_of_service")
 */
class TermsOfServiceController
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TermsOfServiceManager */
    private $manager;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        TermsOfServiceManager $manager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * @Route("/", name="apiv2_platform_terms_of_service", methods={"GET"})
     */
    public function getCurrentAction(Request $request)
    {
        $terms = null;
        if ($this->manager->isActive()) {
            $terms = $this->manager->getLocalizedTermsOfService($request->getLocale());
        }

        return new JsonResponse($terms);
    }

    /**
     * @Route("/accept", name="apiv2_platform_terms_of_service_accept", methods={"PUT"})
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
