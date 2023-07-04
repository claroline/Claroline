<?php

namespace Claroline\AnnouncementBundle\Controller;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages announces of an announcement resource.
 *
 * @Route("/announcement/{aggregateId}", options={"expose"=true})
 *
 * @EXT\ParamConverter("aggregate", class="Claroline\AnnouncementBundle\Entity\AnnouncementAggregate", options={"mapping": {"aggregateId": "uuid"}})
 */
class AnnouncementAggregateController
{
    use RequestDecoderTrait;

    /** @var Crud */
    private $crud;

    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        Crud $crud,
        SerializerProvider $serializer
    ) {
        $this->crud = $crud;
        $this->serializer = $serializer;
    }

    public function getClass(): string
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
