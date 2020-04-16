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

use Claroline\AuthenticationBundle\Security\Authentication\Token\ViewAsToken;
use Claroline\CoreBundle\Library\Security\TokenUpdater;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\Role;

class ViewAsListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var RoleManager */
    private $roleManager;
    /** @var TokenUpdater */
    private $tokenUpdater;
    /** @var UserManager */
    private $userManager;

    /**
     * ViewAsListener constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TokenStorageInterface         $tokenStorage
     * @param EntityManager                 $em
     * @param RoleManager                   $roleManager
     * @param TokenUpdater                  $tokenUpdater
     * @param UserManager                   $userManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        EntityManager $em,
        RoleManager $roleManager,
        TokenUpdater $tokenUpdater,
        UserManager $userManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->em = $em;
        $this->roleManager = $roleManager;
        $this->tokenUpdater = $tokenUpdater;
        $this->userManager = $userManager;
    }

    public function onViewAs(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->query->all();

        if (array_key_exists('view_as', $attributes)) {
            // first, if we're already usurping a user role with the sf2 function, we cancel this.
            // ROLE_PREVIOUS_ADMIN means we're an administrator usurping a user account.

            if ($this->authorization->isGranted('ROLE_PREVIOUS_ADMIN')) {
                $this->tokenUpdater->cancelUserUsurpation($this->tokenStorage->getToken());
            }

            // then we go as intended
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

                $tokenRoles = array_map(function (Role $role) {
                    return $role->getRole();
                }, $this->tokenStorage->getToken()->getRoles());

                if (!in_array('ROLE_USURPATE_WORKSPACE_ROLE', $tokenRoles)) {
                    if ($this->authorization->isGranted($managerRole->getName())) {
                        if ('ROLE_ANONYMOUS' === $baseRole) {
                            throw new \Exception('No implementation yet');
                        } else {
                            // TODO : we may use a standard token. To check
                            $token = new ViewAsToken(
                              ['ROLE_USER', $viewAs, 'ROLE_USURPATE_WORKSPACE_ROLE']
                            );
                            // store original user id to retrieve it at end
                            $token->setAttribute('user_uuid', $this->tokenStorage->getToken()->getUser()->getUuid());
                            // replace user
                            $token->setUser($this->userManager->getDefaultClarolineUser());

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
