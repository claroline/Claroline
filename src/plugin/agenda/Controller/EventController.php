<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Controller;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/event")
 */
class EventController extends AbstractCrudController
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

    public function getClass()
    {
        return Event::class;
    }

    public function getName()
    {
        return 'event';
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
            if ('anon.' !== $user) {
                $hiddenFilters['user'] = $user->getUuid();
            } else {
                $hiddenFilters['anonymous'] = true;
            }
        }

        return $hiddenFilters;
    }
}
