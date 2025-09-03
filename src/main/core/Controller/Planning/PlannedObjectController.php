<?php

namespace Claroline\CoreBundle\Controller\Planning;

use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/planned_object', name: 'apiv2_planned_object_')]
class PlannedObjectController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly RequestStack $requestStack
    ) {
        $this->authorization = $authorization;
    }

    public static function getName(): string
    {
        return 'planned_object';
    }

    public static function getClass(): string
    {
        return PlannedObject::class;
    }

    public function getIgnore(): array
    {
        return ['create', 'update'];
    }

    #[Route(path: '/planning/{planningId}', name: 'planning_list', methods: ['GET'])]
    public function listByPlanningAction(
        string $planningId,
        #[MapQueryString]
        ?FinderQuery $finderQuery = new FinderQuery()
    ): StreamedJsonResponse {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        $finderQuery->addFilter('planning', $planningId);

        $plannedObjects = $this->crud->search(PlannedObject::class, $finderQuery, [SerializerInterface::SERIALIZE_LIST]);

        return $plannedObjects->toResponse();
    }
}
