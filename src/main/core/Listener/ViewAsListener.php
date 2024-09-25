<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\AuthenticationBundle\Security\Authentication\Authenticator;
use Claroline\CoreBundle\Event\CatalogEvents\SecurityEvents;
use Claroline\CoreBundle\Event\Security\ViewAsEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ViewAsListener
{
    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Authenticator $authenticator,
        private readonly RoleManager $roleManager,
    ) {
    }

    public function onViewAs(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $attributes = $request->query->all();

        if ($event->isMainRequest() && array_key_exists('view_as', $attributes)) {
            // first, if we're already usurping a user role with the sf2 function, we cancel this.
            // IS_IMPERSONATOR means we're an administrator usurping a user account.

            if ($this->authorization->isGranted('IS_IMPERSONATOR')) {
                $this->authenticator->cancelUserUsurpation($this->tokenStorage->getToken());
            }

            // then we go as intended
            $viewAs = $attributes['view_as'];
            if ('exit' === $viewAs) {
                if ($this->authorization->isGranted('ROLE_USURPATE_WORKSPACE_ROLE')) {
                    $this->authenticator->cancelUsurpation($this->tokenStorage->getToken());
                }
            } else {
                $role = $this->roleManager->getRoleByName($viewAs);
                if (null === $role) {
                    throw new \Exception("The role {$viewAs} does not exists");
                }

                if (!$this->authorization->isGranted('ROLE_USURPATE_WORKSPACE_ROLE')) {
                    // we are not already usurping a workspace role
                    if ($this->authorization->isGranted('ADMINISTRATE', $role->getWorkspace())) {
                        // we have the right to usurp one the workspace role
                        $this->authenticator->createToken($this->tokenStorage->getToken()?->getUser(), [PlatformRoles::USER, $viewAs, 'ROLE_USURPATE_WORKSPACE_ROLE']);

                        $event = new ViewAsEvent($this->tokenStorage->getToken()?->getUser(), $viewAs);
                        $this->eventDispatcher->dispatch($event, SecurityEvents::VIEW_AS);
                    } else {
                        throw new AccessDeniedException(sprintf('You do not have the right to usurp the role %s.', $viewAs));
                    }
                }
            }
        }
    }
}
