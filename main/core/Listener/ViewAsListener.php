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

use Claroline\AuthenticationBundle\Security\Authentication\TokenUpdater;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ViewAsListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var TokenUpdater */
    private $tokenUpdater;
    /** @var RoleManager */
    private $roleManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        TokenUpdater $tokenUpdater,
        RoleManager $roleManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->tokenUpdater = $tokenUpdater;
        $this->roleManager = $roleManager;
    }

    public function onViewAs(RequestEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->query->all();

        if ($event->isMasterRequest() && array_key_exists('view_as', $attributes)) {
            // first, if we're already usurping a user role with the sf2 function, we cancel this.
            // ROLE_PREVIOUS_ADMIN means we're an administrator usurping a user account.

            if ($this->authorization->isGranted('ROLE_PREVIOUS_ADMIN')) {
                $this->tokenUpdater->cancelUserUsurpation($this->tokenStorage->getToken());
            }

            // then we go as intended
            $viewAs = $attributes['view_as'];
            if ('exit' === $viewAs) {
                if ($this->authorization->isGranted('ROLE_USURPATE_WORKSPACE_ROLE')) {
                    $this->tokenUpdater->cancelUsurpation($this->tokenStorage->getToken());
                }
            } else {
                $baseRole = substr($viewAs, 0, strripos($viewAs, '_'));
                $role = $this->roleManager->getRoleByName($viewAs);
                if (null === $role) {
                    throw new \Exception("The role {$viewAs} does not exists");
                }

                if (!in_array('ROLE_USURPATE_WORKSPACE_ROLE', $this->tokenStorage->getToken()->getRoleNames())) {
                    // we are not already usurping a workspace role
                    if ($this->authorization->isGranted('ADMINISTRATE', $role->getWorkspace())) {
                        // we have the right to usurp one the workspace role
                        if ('ROLE_ANONYMOUS' === $baseRole) {
                            $this->tokenUpdater->createAnonymous();
                        } else {
                            $token = new UsernamePasswordToken(
                                $this->tokenStorage->getToken()->getUser(),
                                null,
                                'main',
                                ['ROLE_USER', $viewAs, 'ROLE_USURPATE_WORKSPACE_ROLE']
                            );

                            // set new token
                            $this->tokenStorage->setToken($token);
                        }
                    } else {
                        throw new AccessDeniedException();
                    }
                }
            }
        }
    }
}
