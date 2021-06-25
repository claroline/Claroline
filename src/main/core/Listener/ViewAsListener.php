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
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\LogBundle\Messenger\Security\Message\ViewAsMessage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ViewAsListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Authenticator */
    private $authenticator;
    /** @var RoleManager */
    private $roleManager;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        Authenticator $authenticator,
        RoleManager $roleManager,
        MessageBusInterface $messageBus,
        TranslatorInterface $translator
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->authenticator = $authenticator;
        $this->roleManager = $roleManager;
        $this->messageBus = $messageBus;
        $this->translator = $translator;
    }

    public function onViewAs(RequestEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->query->all();

        if ($event->isMasterRequest() && array_key_exists('view_as', $attributes)) {
            // first, if we're already usurping a user role with the sf2 function, we cancel this.
            // ROLE_PREVIOUS_ADMIN means we're an administrator usurping a user account.

            if ($this->authorization->isGranted('ROLE_PREVIOUS_ADMIN')) {
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

                if (!in_array('ROLE_USURPATE_WORKSPACE_ROLE', $this->tokenStorage->getToken()->getRoleNames())) {
                    // we are not already usurping a workspace role
                    if (in_array($viewAs, ['ROLE_USER', 'ROLE_ANONYMOUS']) || $this->authorization->isGranted('ADMINISTRATE', $role->getWorkspace())) {
                        // we have the right to usurp one the workspace role
                        if ('ROLE_ANONYMOUS' === $viewAs) {
                            $this->authenticator->createAnonymousToken();
                        } else {
                            $this->authenticator->createToken($this->tokenStorage->getToken()->getUser(), ['ROLE_USER', $viewAs, 'ROLE_USURPATE_WORKSPACE_ROLE']);
                            $user = $this->tokenStorage->getToken()->getUser();
                            $this->messageBus->dispatch(new ViewAsMessage(
                                $user->getId(),
                                $user->getId(),
                               $this->translator->trans('viewAs', ['username' => $user->getUsername(), 'role' => $viewAs], 'security')
                            ));
                        }
                    } else {
                        throw new AccessDeniedException(sprintf('You do not have the right to usurp the role %s.', $viewAs));
                    }
                }
            }
        }
    }
}
