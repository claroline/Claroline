<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Entity\Recording;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\API\Serializer\Resource\AbstractResourceSerializer;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/bbb")
 */
class AdministrationController
{
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var PlatformConfigurationHandler */
    private $config;
    /** @var ObjectManager */
    private $om;
    /** @var Crud */
    private $crud;
    /** @var ToolManager */
    private $toolManager;
    /** @var BBBManager */
    private $bbbManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        PlatformConfigurationHandler $config,
        ObjectManager $om,
        Crud $crud,
        ToolManager $toolManager,
        BBBManager $bbbManager
    ) {
        $this->authorization = $authorization;
        $this->config = $config;
        $this->om = $om;
        $this->crud = $crud;
        $this->toolManager = $toolManager;
        $this->bbbManager = $bbbManager;
    }

    /**
     * @Route("/info", name="apiv2_bbb_integration_info")
     */
    public function getInfoAction(): JsonResponse
    {
        $this->checkAccess();

        $meetings = $this->bbbManager->fetchActiveMeetings();

        $participantsCount = 0;
        foreach ($meetings as $meeting) {
            $participantsCount += $meeting['participantCount'];
        }

        return new JsonResponse([
            'maxMeetings' => $this->config->getParameter('bbb.max_meetings'),
            'maxMeetingParticipants' => $this->config->getParameter('bbb.max_meeting_participants'),
            'maxParticipants' => $this->config->getParameter('bbb.max_participants'),
            'activeMeetings' => $meetings,
            'participantsCount' => $participantsCount,
            'allowRecords' => $this->config->getParameter('bbb.allow_records'),
            'servers' => $this->bbbManager->getServers(false),
        ]);
    }

    /**
     * @Route("/meetings", name="apiv2_bbb_integration_meetings", methods={"GET"})
     */
    public function listMeetingsAction(Request $request): JsonResponse
    {
        $this->checkAccess();

        return new JsonResponse(
            $this->crud->list(BBB::class, $request->query->all(), [AbstractResourceSerializer::SERIALIZE_NODE])
        );
    }

    /**
     * @Route("/meetings/end", name="apiv2_bbb_integration_meetings_end", methods={"PUT"})
     */
    public function endMeetingsAction(Request $request): JsonResponse
    {
        $this->checkAccess();

        /** @var BBB[] $users */
        $meetings = $this->decodeIdsString($request, BBB::class);
        foreach ($meetings as $meeting) {
            $this->bbbManager->endMeeting($meeting);
        }

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/recordings", name="apiv2_bbb_integration_recordings_list", methods={"GET"})
     */
    public function listRecordingsAction(Request $request): JsonResponse
    {
        $this->checkAccess();

        return new JsonResponse(
            $this->crud->list(Recording::class, $request->query->all())
        );
    }

    /**
     * @Route("/recordings", name="apiv2_bbb_integration_recordings_sync", methods={"POST"})
     */
    public function syncRecordingsAction(): JsonResponse
    {
        $this->checkAccess();

        $this->bbbManager->syncAllRecordings();

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/recordings", name="apiv2_bbb_integration_recordings_delete", methods={"DELETE"})
     */
    public function deleteRecordingsAction(Request $request): JsonResponse
    {
        $this->checkAccess();

        $this->crud->deleteBulk(
            $this->decodeIdsString($request, Recording::class)
        );

        return new JsonResponse(null, 204);
    }

    private function checkAccess()
    {
        $integrationTool = $this->toolManager->getAdminToolByName('integration');
        if (is_null($integrationTool) || !$this->authorization->isGranted('OPEN', $integrationTool)) {
            throw new AccessDeniedException();
        }
    }
}
