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

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Entity\Location\Material;
use Claroline\CoreBundle\Entity\Location\MaterialBooking;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/booking_material")
 */
class MaterialController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
    }

    public function getName(): string
    {
        return 'booking_material';
    }

    public function getClass(): string
    {
        return Material::class;
    }

    /**
     * @Route("/{material}/booking", name="apiv2_booking_material_book", methods={"POST"})
     *
     * @EXT\ParamConverter("material", class="Claroline\CoreBundle\Entity\Location\Material", options={"mapping": {"material": "uuid"}})
     */
    public function bookAction(Material $material, Request $request): JsonResponse
    {
        $this->canBook();

        // TODO : check availability
        $this->om->startFlushSuite();

        $data = $this->decodeRequest($request);
        /** @var MaterialBooking $object */
        $object = $this->crud->create(MaterialBooking::class, $data, [Crud::THROW_EXCEPTION]);
        $object->setMaterial($material);

        $this->om->endFlushSuite();

        return new JsonResponse($this->serializer->serialize($object), 201);
    }

    /**
     * @Route("/{material}/booking", name="apiv2_booking_material_list_booking", methods={"GET"})
     *
     * @EXT\ParamConverter("material", class="Claroline\CoreBundle\Entity\Location\Material", options={"mapping": {"material": "uuid"}})
     */
    public function listBookingAction(Material $material, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $material, [], true);

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'material' => $material,
        ];

        return new JsonResponse(
            $this->crud->list(MaterialBooking::class, $query)
        );
    }

    /**
     * @Route("/{$material}/booking", name="apiv2_booking_material_remove_booking", methods={"DELETE"})
     *
     * @EXT\ParamConverter("$material", class="Claroline\CoreBundle\Entity\Location\Material", options={"mapping": {"$material": "uuid"}})
     */
    public function deleteBookingAction(Material $material, Request $request): JsonResponse
    {
        $this->canBook();

        $this->crud->deleteBulk(
            $this->decodeIdsString($request, MaterialBooking::class)
        );

        return new JsonResponse(null, 204);
    }

    private function canBook(): void
    {
        $bookingTool = $this->toolManager->getOrderedTool('locations', DesktopContext::getName());

        $this->checkPermission('BOOK', $bookingTool, [], true);
    }
}
