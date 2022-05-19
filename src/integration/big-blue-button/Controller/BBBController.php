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

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\BigBlueButtonBundle\Entity\BBB;
use Claroline\BigBlueButtonBundle\Entity\Recording;
use Claroline\BigBlueButtonBundle\Manager\BBBManager;
use Claroline\CoreBundle\Library\RoutingHelper;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/bbb")
 */
class BBBController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var BBBManager */
    private $bbbManager;
    /** @var UrlGeneratorInterface */
    private $router;
    /** @var RoutingHelper */
    private $routingHelper;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        BBBManager $bbbManager,
        UrlGeneratorInterface $router,
        RoutingHelper $routingHelper
    ) {
        $this->authorization = $authorization;
        $this->bbbManager = $bbbManager;
        $this->router = $router;
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
     * @Route("/{id}/meeting", name="apiv2_bbb_meeting_create", methods={"POST"})
     * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
     */
    public function createMeetingAction(BBB $bbb): JsonResponse
    {
        $this->checkPermission('OPEN', $bbb->getResourceNode(), [], true);

        $created = $this->bbbManager->createMeeting($bbb);
        if ($created) {
            return new JsonResponse([
                'joinStatus' => $this->bbbManager->canJoinMeeting($bbb),
            ], 201);
        }

        return new JsonResponse(null, 404);
    }

    /**
     * @Route("/{id}/meeting/join/{username}", name="apiv2_bbb_meeting_join")
     * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
     */
    public function joinMeetingAction(BBB $bbb, string $username = null): RedirectResponse
    {
        $this->checkPermission('OPEN', $bbb->getResourceNode(), [], true);

        $moderator = $this->checkPermission('ADMINISTRATE', $bbb->getResourceNode());

        $errors = $this->bbbManager->canJoinMeeting($bbb);
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
     * @Route("/{id}/meeting/end", name="apiv2_bbb_meeting_end", methods={"PUT"})
     * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
     */
    public function endMeetingAction(BBB $bbb): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $bbb->getResourceNode(), [], true);

        $this->bbbManager->endMeeting($bbb);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/{id}/meeting/moderators/check", name="apiv2_bbb_meeting_moderators_check", methods={"GET"})
     * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
     */
    public function meetingModeratorsCheckAction(BBB $bbb): JsonResponse
    {
        $this->checkPermission('OPEN', $bbb->getResourceNode(), [], true);

        return new JsonResponse(
            $this->bbbManager->hasMeetingModerators($bbb)
        );
    }

    /**
     * @Route("/{id}/recordings", name="apiv2_bbb_meeting_recordings_list", methods={"GET"})
     * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
     */
    public function listRecordingsAction(BBB $bbb, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $bbb->getResourceNode(), [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'meeting' => $bbb,
        ];

        return new JsonResponse(
            $this->finder->search(Recording::class, $query)
        );
    }

    /**
     * @Route("/{id}/recordings", name="apiv2_bbb_meeting_recording_delete", methods={"DELETE"})
     * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
     */
    public function deleteRecordingsAction(BBB $bbb, Request $request): JsonResponse
    {
        $this->crud->deleteBulk(
            $this->decodeIdsString($request, Recording::class)
        );

        return new JsonResponse(null, 204);
    }
}
