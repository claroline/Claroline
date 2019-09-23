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

use Claroline\CoreBundle\Library\Security\Token\ViewAsToken;
use Claroline\CoreBundle\Library\Security\TokenUpdater;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service
 */
class ViewAsListener
{
    private $tokenStorage;
    private $authorization;
    private $roleManager;
    private $tokenUpdater;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "em"            = @DI\Inject("doctrine.orm.entity_manager"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "tokenUpdater"  = @DI\Inject("claroline.security.token_updater"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        EntityManager $em,
        UserManager $userManager,
        RoleManager $roleManager,
        TokenUpdater $tokenUpdater
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->em = $em;
        $this->roleManager = $roleManager;
        $this->tokenUpdater = $tokenUpdater;
        $this->userManager = $userManager;
    }

    /**
     * @DI\Observe("kernel.request")
     */
    public function onViewAs(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->query->all();

        if (array_key_exists('view_as', $attributes)) {
            //first, if we're already usurpating a user role with the sf2 function, we cancel this.
            //ROLE_PREVIOUS_ADMIN means we're an administrator usurpating a user account.

            if ($this->authorization->isGranted('ROLE_PREVIOUS_ADMIN')) {
                $this->tokenUpdater->cancelUserUsurpation($this->tokenStorage->getToken());
            }

            //then we go as intended
            $viewAs = $attributes['view_as'];
            if ('exit' === $viewAs) {
                if ($this->authorization->isGranted('ROLE_USURPATE_WORKSPACE_ROLE')) {
                    $viewAsToken = $this->tokenStorage->getToken();
                    $user = $this->em->getRepository('ClarolineCoreBundle:User')
                        ->findOneBy(['uuid' => $viewAsToken->getAttribute('user_uuid')]);
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->tokenStorage->setToken($token);
                }
            } else {
                $baseRole = substr($viewAs, 0, strripos($viewAs, '_'));
                $role = $this->roleManager->getRoleByName($viewAs);

                if (null === $role) {
                    throw new \Exception("The role {$viewAs} does not exists");
                }

                $managerRole = $this->roleManager->getManagerRole($role->getWorkspace());

                $tokenRoles = array_map(function ($role) {
                    return $role->getRole();
                }, $this->tokenStorage->getToken()->getRoles());

                if (!in_array('ROLE_USURPATE_WORKSPACE_ROLE', $tokenRoles)) {
                    if ($this->authorization->isGranted($managerRole->getName())) {
                        if ('ROLE_ANONYMOUS' === $baseRole) {
                            throw new \Exception('No implementation yet');
                        } else {
                            $token = new ViewAsToken(
                              ['ROLE_USER', $viewAs, 'ROLE_USURPATE_WORKSPACE_ROLE']
                            );
                            $token->setUser($this->userManager->getDefaultClarolineUser());
                            $token->setAttribute('user_uuid', $this->tokenStorage->getToken()->getUser()->getUuid());
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
