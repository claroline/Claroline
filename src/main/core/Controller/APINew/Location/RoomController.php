<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\APINew\Location;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Location\Room;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/location_room")
 */
class RoomController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(AuthorizationCheckerInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    public function getName()
    {
        return 'location_room';
    }

    public function getClass()
    {
        return Room::class;
    }

    /**
     * @Route("/{room}/events", name="apiv2_location_room_list_event", methods={"GET"})
     * @EXT\ParamConverter("room", class="ClarolineCoreBundle:Location\Room", options={"mapping": {"room": "uuid"}})
     */
    public function listEventsAction(Room $room, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $room, [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'room' => $room,
        ];

        return new JsonResponse(
            $this->finder->search(PlannedObject::class, $query)
        );
    }
}
