<?php

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Serializer\AnnouncementAggregateSerializer;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Manages announces of an announcement resource.
 *
 * @Route("/announcement/{aggregateId}", options={"expose"=true})
 * @EXT\ParamConverter("aggregate", class="Claroline\AnnouncementBundle\Entity\AnnouncementAggregate", options={"mapping": {"aggregateId": "uuid"}})
 */
class AnnouncementAggregateController
{
    use RequestDecoderTrait;
    use PermissionCheckerTrait;

    /** @var AnnouncementAggregateSerializer */
    private $serializer;

    /** @var Crud */
    private $crud;

    /** @var ObjectManager */
    private $om;

    /** @var FinderProvider */
    private $finder;

    public function __construct(
        AnnouncementAggregateSerializer $serializer,
        Crud $crud,
        ObjectManager $om,
        FinderProvider $finder,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->serializer = $serializer;
        $this->crud = $crud;
        $this->om = $om;
        $this->finder = $finder;
        $this->authorization = $authorization;
    }

    public function getClass()
    {
        return AnnouncementAggregate::class;
    }

    /**
     * Updates an existing announce.
     *
     * @Route("/", name="claro_announcement_aggregate_update", methods={"PUT"})
     */
    public function updateAction(AnnouncementAggregate $aggregate, Request $request): JsonResponse
    {
        $this->crud->update($aggregate, $this->decodeRequest($request), [Crud::THROW_EXCEPTION]);

        return new JsonResponse(
            $this->serializer->serialize($aggregate)
        );
    }
}
