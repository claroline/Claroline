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
 * @EXT\ParamConverter("bbb", class="Claroline\BigBlueButtonBundle\Entity\BBB", options={"mapping": {"id": "uuid"}})
 */
#[Route(path: '/bbb/{id}')]
class BBBController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly Crud $crud,
        private readonly BBBManager $bbbManager,
        private readonly UrlGeneratorInterface $router,
        private readonly RoutingHelper $routingHelper
    ) {
        $this->authorization = $authorization;
    }

    #[Route(path: '/meeting', name: 'apiv2_bbb_meeting_create', methods: ['POST'])]
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

    #[Route(path: '/meeting/join/{username}', name: 'apiv2_bbb_meeting_join')]
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

        return new RedirectResponse(
            $this->routingHelper->resourceUrl($bbb->getResourceNode())
        );
    }

    #[Route(path: '/meeting/end', name: 'apiv2_bbb_meeting_end', methods: ['PUT'])]
    public function endMeetingAction(BBB $bbb): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $bbb->getResourceNode(), [], true);

        $this->bbbManager->endMeeting($bbb);

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/meeting/moderators/check', name: 'apiv2_bbb_meeting_moderators_check', methods: ['GET'])]
    public function meetingModeratorsCheckAction(BBB $bbb): JsonResponse
    {
        $this->checkPermission('OPEN', $bbb->getResourceNode(), [], true);

        return new JsonResponse(
            $this->bbbManager->hasMeetingModerators($bbb)
        );
    }

    #[Route(path: '/recordings', name: 'apiv2_bbb_meeting_recordings_list', methods: ['GET'])]
    public function listRecordingsAction(BBB $bbb, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $bbb->getResourceNode(), [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'meeting' => $bbb,
        ];

        return new JsonResponse(
            $this->crud->list(Recording::class, $query)
        );
    }

    #[Route(path: '/recordings', name: 'apiv2_bbb_meeting_recording_delete', methods: ['DELETE'])]
    public function deleteRecordingsAction(BBB $bbb, Request $request): JsonResponse
    {
        $this->crud->deleteBulk(
            $this->decodeIdsString($request, Recording::class)
        );

        return new JsonResponse(null, 204);
    }
}
