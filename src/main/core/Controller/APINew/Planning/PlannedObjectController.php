<?php

namespace Claroline\CoreBundle\Controller\APINew\Planning;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/planned_object")
 */
class PlannedObjectController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->requestStack = $requestStack;
    }

    public function getName(): string
    {
        return 'planned_object';
    }

    public function getClass()
    {
        return PlannedObject::class;
    }

    public function getIgnore()
    {
        return ['create', 'update'];
    }

    protected function getDefaultHiddenFilters(): array
    {
        $hiddenFilters = [];

        $query = $this->requestStack->getCurrentRequest()->query->all();

        // get start & end date and add them to the hidden filters list
        $hiddenFilters['inRange'] = [$query['start'] ?? null, $query['end'] ?? null];

        if (!isset($query['filters']['workspaces'])) {
            /** @var User $user */
            $user = $this->tokenStorage->getToken()->getUser();
            if ($user instanceof User) {
                $hiddenFilters['user'] = $user->getUuid();
            } else {
                $hiddenFilters['anonymous'] = true;
            }
        }

        return $hiddenFilters;
    }
}
