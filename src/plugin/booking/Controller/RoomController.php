<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BookingBundle\Controller;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Controller\RequestDecoderTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BookingBundle\Entity\RoomBooking;
use Claroline\CoreBundle\Entity\Location\Room;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/booking_room")
 */
class RoomController
{
    use PermissionCheckerTrait;
    use RequestDecoderTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var SerializerProvider */
    private $serializer;
    /** @var Crud */
    private $crud;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        FinderProvider $finder,
        SerializerProvider $serializer,
        Crud $crud
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->finder = $finder;
        $this->serializer = $serializer;
        $this->crud = $crud;
    }

    /**
     * @Route("/{room}/booking", name="apiv2_booking_room_book", methods={"POST"})
     * @EXT\ParamConverter("room", class="Claroline\CoreBundle\Entity\Location\Room", options={"mapping": {"room": "uuid"}})
     */
    public function bookAction(Room $room, Request $request): JsonResponse
    {
        $this->canBook();

        // TODO : check availability
        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);
        /** @var RoomBooking $object */
        $object = $this->crud->create(RoomBooking::class, $data, [Crud::THROW_EXCEPTION]);
        $object->setRoom($room);

        $this->om->endFlushSuite();

        return new JsonResponse($this->serializer->serialize($object), 201);
    }

    /**
     * @Route("/{room}/booking", name="apiv2_booking_room_list_booking", methods={"GET"})
     * @EXT\ParamConverter("room", class="Claroline\CoreBundle\Entity\Location\Room", options={"mapping": {"room": "uuid"}})
     */
    public function listBookingAction(Room $room, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $room, [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'room' => $room,
        ];

        return new JsonResponse(
            $this->finder->search(RoomBooking::class, $query)
        );
    }

    /**
     * @Route("/{room}/booking", name="apiv2_booking_room_remove_booking", methods={"DELETE"})
     * @EXT\ParamConverter("room", class="Claroline\CoreBundle\Entity\Location\Room", options={"mapping": {"room": "uuid"}})
     */
    public function deleteBookingAction(Room $room, Request $request): JsonResponse
    {
        $this->canBook();

        $this->crud->deleteBulk(
            $this->decodeIdsString($request, RoomBooking::class)
        );

        return new JsonResponse(null, 204);
    }

    private function canBook()
    {
        /** @var OrderedToolRepository $orderedToolRepo */
        $orderedToolRepo = $this->om->getRepository(OrderedTool::class);

        $bookingTool = $orderedToolRepo->findOneByNameAndDesktop('booking');

        $this->checkPermission('BOOK', $bookingTool, [], true);
    }
}
