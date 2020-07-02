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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\CurlManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @EXT\Route("/bbb")
 */
class BBBController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var BBBManager */
    private $bbbManager;
    /** @var CurlManager */
    private $curlManager;
    /** @var ParametersSerializer */
    private $parametersSerializer;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var ToolManager */
    private $toolManager;
    /** @var RoutingHelper */
    private $routingHelper;

    /**
     * @param AuthorizationCheckerInterface $authorization
     * @param BBBManager                    $bbbManager
     * @param CurlManager                   $curlManager
     * @param ParametersSerializer          $parametersSerializer
     * @param UrlGeneratorInterface         $router
     * @param ToolManager                   $toolManager
     * @param RoutingHelper                 $routingHelper
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BBBManager $bbbManager,
        CurlManager $curlManager,
        ParametersSerializer $parametersSerializer,
        UrlGeneratorInterface $router,
        ToolManager $toolManager,
        RoutingHelper $routingHelper
    ) {
        $this->authorization = $authorization;
        $this->bbbManager = $bbbManager;
        $this->curlManager = $curlManager;
        $this->parametersSerializer = $parametersSerializer;
        $this->router = $router;
        $this->toolManager = $toolManager;
        $this->routingHelper = $routingHelper;
    }

    public function getName()
    {
        return 'bbb';
    }

    public function getClass()
    {
        return BBB::class;
    }

    public function getIgnore()
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list', 'get', 'create', 'deleteBulk'];
    }

    /**
     * @EXT\Route("/meetings/list", name="apiv2_bbb_moderator_meetings_list")
     *
     * @return JsonResponse
     */
    public function moderatorMeetingsListAction()
    {
        $integrationTool = $this->toolManager->getAdminToolByName('integration');
        if (is_null($integrationTool) || !$this->authorization->isGranted('OPEN', $integrationTool)) {
            throw new AccessDeniedException();
        }

        $meetings = $this->bbbManager->fetchActiveMeetings();
        $participantsCount = 0;

        foreach ($meetings as $meeting) {
            $participantsCount += $meeting['participantCount'];
        }

        $parameters = $this->parametersSerializer->serialize([Options::SERIALIZE_MINIMAL]);

        return new JsonResponse([
            'maxMeetings' => $parameters['bbb']['max_meetings'],
            'maxMeetingParticipants' => $parameters['bbb']['max_meeting_participants'],
            'maxParticipants' => $parameters['bbb']['max_participants'],
            'activeMeetingsCount' => count($meetings),
            'participantsCount' => $participantsCount,
            'meetings' => $meetings,
        ]);
    }

    /**
     * @EXT\Route("/{id}/meeting", name="apiv2_bbb_meeting_create")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("bbb", class="ClarolineBigBlueButtonBundle:BBB", options={"mapping": {"id": "uuid"}})
     *
     * @param BBB $bbb
     *
     * @return JsonResponse
     */
    public function createMeetingAction(BBB $bbb)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);
        if (!$this->authorization->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException();
        }

        $created = $this->bbbManager->createMeeting($bbb);
        if ($created) {
            $moderator = $this->authorization->isGranted('ADMINISTRATE', $collection);

            return new JsonResponse([
                'joinStatus' => $this->bbbManager->canJoinMeeting($bbb, $moderator),
            ], 201);
        }

        return new JsonResponse(null, 404);
    }

    /**
     * @EXT\Route("/{id}/meeting/join/{username}", name="apiv2_bbb_meeting_join")
     * @EXT\ParamConverter("bbb", class="ClarolineBigBlueButtonBundle:BBB", options={"mapping": {"id": "uuid"}})
     *
     * @param BBB    $bbb
     * @param string $username
     *
     * @return RedirectResponse
     */
    public function joinMeetingAction(BBB $bbb, $username = null)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);
        if (!$this->authorization->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException();
        }

        $moderator = $this->authorization->isGranted('ADMINISTRATE', $collection);

        $errors = $this->bbbManager->canJoinMeeting($bbb, $moderator);
        if (empty($errors)) {
            $url = $this->bbbManager->getMeetingUrl($bbb, $moderator, $username);
            if ($url) {
                return new RedirectResponse($url);
            }
        }

        // TODO : manage opening in iframe
        return new RedirectResponse(
            $this->routingHelper->resourceUrl($bbb->getResourceNode())
        );
    }

    /**
     * @EXT\Route("/{id}/meeting/end", name="apiv2_bbb_meeting_end")
     * @EXT\ParamConverter("bbb", class="ClarolineBigBlueButtonBundle:BBB", options={"mapping": {"id": "uuid"}})
     *
     * @param BBB $bbb
     *
     * @return JsonResponse
     */
    public function meetingEndAction(BBB $bbb)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);
        if (!$this->authorization->isGranted('ADMINISTRATE', $collection)) {
            throw new AccessDeniedException();
        }

        $this->bbbManager->endMeeting($bbb);

        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\Route("/{id}/meeting/moderators/check", name="apiv2_bbb_meeting_moderators_check")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("bbb", class="ClarolineBigBlueButtonBundle:BBB", options={"mapping": {"id": "uuid"}})
     *
     * @param BBB $bbb
     *
     * @return JsonResponse
     */
    public function meetingModeratorsCheckAction(BBB $bbb)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);
        if (!$this->authorization->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->bbbManager->hasMeetingModerators($bbb)
        );
    }

    /**
     * @EXT\Route("/{id}/recordings", name="apiv2_bbb_meeting_recordings_list")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("bbb", class="ClarolineBigBlueButtonBundle:BBB", options={"mapping": {"id": "uuid"}})
     *
     * @param BBB $bbb
     *
     * @return JsonResponse
     */
    public function recordingsListAction(BBB $bbb)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);
        if (!$this->authorization->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->bbbManager->getRecordings($bbb)
        );
    }

    /**
     * @EXT\Route("/{id}/recordings/{recordId}", name="apiv2_bbb_meeting_recording_delete")
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter("bbb", class="ClarolineBigBlueButtonBundle:BBB", options={"mapping": {"id": "uuid"}})
     *
     * @param BBB    $bbb
     * @param string $recordId
     *
     * @return JsonResponse
     */
    public function recordingDeleteAction(BBB $bbb, $recordId)
    {
        $collection = new ResourceCollection([$bbb->getResourceNode()]);
        if (!$this->authorization->isGranted('ADMINISTRATE', $collection)) {
            throw new AccessDeniedException();
        }

        $this->bbbManager->deleteMeetingRecordings($bbb, [$recordId]);

        return new JsonResponse(null, 204);
    }
}
