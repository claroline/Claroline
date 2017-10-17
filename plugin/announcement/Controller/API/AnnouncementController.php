<?php

namespace Claroline\AnnouncementBundle\Controller\API;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Manager\AnnouncementManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages announces of an announcement resource.
 *
 * @EXT\Route("/{aggregateId}", options={"expose"=true})
 * @EXT\ParamConverter(
 *      "aggregate",
 *      class="ClarolineAnnouncementBundle:AnnouncementAggregate",
 *      options={"mapping": {"aggregateId": "uuid"}}
 * )
 */
class AnnouncementController
{
    use PermissionCheckerTrait;

    /** @var AnnouncementManager */
    private $manager;

    /**
     * AnnouncementController constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.announcement_manager")
     * })
     *
     * @param AnnouncementManager $manager
     */
    public function __construct(AnnouncementManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Creates a new announce.
     *
     * @EXT\Route("/", name="claro_announcement_create")
     * @EXT\Method("POST")
     *
     * @param AnnouncementAggregate $aggregate
     * @param Request               $request
     *
     * @return JsonResponse
     */
    public function createAction(AnnouncementAggregate $aggregate, Request $request)
    {
        $this->checkPermission('CREATE', $aggregate->getResourceNode(), [], true);

        try {
            $announcement = $this->manager->create($aggregate, json_decode($request->getContent(), true));

            return new JsonResponse(
                $this->manager->serialize($announcement),
                201
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Updates an existing announce.
     *
     * @EXT\Route("/{id}", name="claro_announcement_update")
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     * @param Request               $request
     *
     * @return JsonResponse
     */
    public function updateAction(AnnouncementAggregate $aggregate, Announcement $announcement, Request $request)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        try {
            $this->manager->update($announcement, json_decode($request->getContent(), true));

            return new JsonResponse(
                $this->manager->serialize($announcement)
            );
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Deletes an announce.
     *
     * @EXT\Route("/{id}", name="claro_announcement_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     *
     * @return JsonResponse
     */
    public function deleteAction(AnnouncementAggregate $aggregate, Announcement $announcement)
    {
        $this->checkPermission('DELETE', $aggregate->getResourceNode(), [], true);

        try {
            $this->manager->delete($announcement);

            return new JsonResponse(null, 204);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }

    /**
     * Sends an announce (in current implementation, it's sent by email).
     *
     * @EXT\Route("/{id}/send", name="claro_announcement_send")
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "announcement",
     *      class="ClarolineAnnouncementBundle:Announcement",
     *      options={"mapping": {"id": "uuid"}}
     * )
     *
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     *
     * @return JsonResponse
     */
    public function sendAction(AnnouncementAggregate $aggregate, Announcement $announcement)
    {
        $this->checkPermission('EDIT', $aggregate->getResourceNode(), [], true);

        try {
            $this->manager->sendMail($announcement);

            return new JsonResponse(null, 204);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }
}
