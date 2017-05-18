<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RemoteUserSynchronizationBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\Authenticator;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\SecurityTokenManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\RemoteUserSynchronizationBundle\Library\Security\Token\UserToken;
use Claroline\RemoteUserSynchronizationBundle\Manager\RemoteUserTokenManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoteUserSynchronizationController extends Controller
{
    private $authenticator;
    private $request;
    private $roleManager;
    private $router;
    private $session;
    private $securityTokenManager;
    private $tokenStorage;
    private $userManager;
    private $remoteUserTokenManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "authenticator"          = @DI\Inject("claroline.authenticator"),
     *     "requestStack"           = @DI\Inject("request_stack"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "session"                = @DI\Inject("session"),
     *     "securityTokenManager"   = @DI\Inject("claroline.manager.security_token_manager"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "remoteUserTokenManager" = @DI\Inject("claroline.manager.remote_user_token_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        Authenticator $authenticator,
        RequestStack $requestStack,
        RoleManager $roleManager,
        RouterInterface $router,
        SessionInterface $session,
        SecurityTokenManager $securityTokenManager,
        TokenStorageInterface $tokenStorage,
        UserManager $userManager,
        RemoteUserTokenManager $remoteUserTokenManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->authenticator = $authenticator;
        $this->request = $requestStack;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->session = $session;
        $this->securityTokenManager = $securityTokenManager;
        $this->tokenStorage = $tokenStorage;
        $this->userManager = $userManager;
        $this->remoteUserTokenManager = $remoteUserTokenManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/remote/user/sync",
     *     name = "claro_sync_remote_user",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function syncRemoteUserAction()
    {
        $request = $this->request->getCurrentRequest();
        $clientIp = $request->getClientIp();
        $port = $request->getPort();
        $datas = $request->request->all();
        $token = isset($datas['token']) ? $datas['token'] : null;
        $clientName = isset($datas['client']) ? $datas['client'] : null;
        $username = isset($datas['username']) ? $datas['username'] : null;
        $firstName = isset($datas['firstName']) ? utf8_encode($datas['firstName']) : null;
        $lastName = isset($datas['lastName']) ? utf8_encode($datas['lastName']) : null;
        $email = isset($datas['email']) ? $datas['email'] : null;
        $password = isset($datas['password']) ? utf8_encode($datas['password']) : null;
        $workspacesTab = isset($datas['workspaces']) ? $datas['workspaces'] : [];
        $userId = isset($datas['userId']) ? $datas['userId'] : null;
        $workspacesAddOnly = isset($datas['workspacesAddOnly']) ? boolval($datas['workspacesAddOnly']) : false;

        if (!empty($token) && !empty($clientName)) {
            $securityToken = $this->securityTokenManager->getSecurityTokenByClientNameAndTokenAndIp($clientName, $token, $clientIp);

            if (is_null($securityToken)) {
                $securityToken = $this->securityTokenManager->getSecurityTokenByClientNameAndTokenAndIp(
                    $clientName,
                    $token,
                    $clientIp.':'.$port
                );
            }
            if (!is_null($securityToken)) {
                if (!empty($username) && !empty($firstName) && !empty($lastName) && !empty($email)) {
                    try {
                        if (!is_null($userId)) {
                            $user = $this->userManager->getUserById($userId);

                            if (is_null($user)) {
                                return new Response('Not found', 404);
                            }
                            $user->setUsername($username);
                            $user->setFirstName($firstName);
                            $user->setLastName($lastName);
                            $user->setMail($email);
                            if (!empty($password)) {
                                $user->setPlainPassword($password);
                            }
                            $this->userManager->persistUser($user);
                        } else {
                            $user = $this->userManager->getUserByUsernameOrMail($username, $email);

                            if (is_null($user)) {
                                if (empty($password)) {
                                    return new Response('Bad Request', 400);
                                }
                                $user = new User();
                                $user->setUsername($username);
                                $user->setFirstName($firstName);
                                $user->setLastName($lastName);
                                $user->setMail($email);
                                $user->setPlainPassword($password);
                                $this->userManager->createUser($user);
                                $user->setIsMailValidated(true);
                            } else {
                                $user->setUsername($username);
                                $user->setFirstName($firstName);
                                $user->setLastName($lastName);
                                $user->setMail($email);

                                if (!empty($password)) {
                                    $user->setPlainPassword($password);
                                }
                            }
                            $this->userManager->persistUser($user);
                        }
                    } catch (\Exception $e) {
                        return new Response('User edition error', 400);
                    }
                    $userRoles = $this->roleManager->getNonPlatformRolesForUser($user);
                    $refreshedRoles = [];

                    foreach ($workspacesTab as $workspaceLine) {
                        $code = key($workspaceLine);
                        $roleKey = $workspaceLine[$code];
                        $role = $this->roleManager->getRoleByWorkspaceCodeAndTranslationKey($code, $roleKey);

                        if (!is_null($role)) {
                            $refreshedRoles[] = $role;
                        }
                    }
                    $this->updateUserRoles($user, $userRoles, $refreshedRoles, $workspacesAddOnly);

                    if (!empty($password)) {
                        $this->authenticator->authenticate($username, $password);
                    }

                    return new JsonResponse($user->getId(), 200);
                } else {
                    return new Response('Bad Request', 400);
                }
            }
        }
        throw new AccessDeniedException();
    }

    /**
     * @EXT\Route(
     *     "/remote/user/token/generate",
     *     name = "claro_generate_remote_user_token",
     *     options={"expose"=true}
     * )
     * @EXT\Method("POST")
     */
    public function generateRemoteUserTokenAction()
    {
        $request = $this->request->getCurrentRequest();
        $clientIp = $request->getClientIp();
        $port = $request->getPort();
        $datas = $request->request->all();
        $token = isset($datas['token']) ? $datas['token'] : null;
        $clientName = isset($datas['client']) ? $datas['client'] : null;
        $userId = isset($datas['userId']) ? $datas['userId'] : null;

        if (!empty($token) && !empty($clientName) && !empty($userId)) {
            $securityToken = $this->securityTokenManager->getSecurityTokenByClientNameAndTokenAndIp($clientName, $token, $clientIp);

            if (is_null($securityToken)) {
                $securityToken = $this->securityTokenManager->getSecurityTokenByClientNameAndTokenAndIp(
                    $clientName,
                    $token,
                    $clientIp.':'.$port
                );
            }
            if (!is_null($securityToken)) {
                $user = $this->userManager->getUserById($userId);

                if (!empty($user) && !$user->hasRole('ROLE_ADMIN')) {
                    $remoteUserToken = $this->remoteUserTokenManager->createRemoteUserToken($user);

                    return new JsonResponse($remoteUserToken->getToken(), 200);
                }
            }
        }
        throw new AccessDeniedException();
    }

    /**
     * @EXT\Route(
     *     "/remote/user/{user}/token/{token}/connect/{workspaceCode}",
     *     name = "claro_remote_user_token_connect",
     *     defaults={"workspaceCode"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @param User   $user
     * @param string $token
     * @param string $workspaceCode
     *
     * @return RedirectResponse|AccessDeniedException
     */
    public function connectRemoteUserWithTokenAction(User $user, $token, $workspaceCode = '')
    {
        $validToken = $this->remoteUserTokenManager->checkRemoteUserToken($user, $token);

        if ($validToken) {
            $workspace = empty($workspaceCode) ? null : $this->workspaceManager->getWorkspaceByCode($workspaceCode);
            $userToken = new UserToken($user);
            $this->tokenStorage->setToken($userToken);

            if (!is_null($workspace)) {
                return new RedirectResponse(
                    $this->router->generate('claro_workspace_open', ['workspaceId' => $workspace->getId()])
                );
            }

            return new RedirectResponse(
                $this->router->generate('claro_desktop_open')
            );
        }
        throw new AccessDeniedException();
    }

    /**
     * Assiociates given user to roles that are in $newRoles and dissociates
     * him/her from roles that are in $currentRoles and not in $newRoles.
     *
     * @param User  $user
     * @param array $currentRoles
     * @param array $newRoles
     * @param bool  $addOnly
     */
    private function updateUserRoles(User $user, array $currentRoles, array $newRoles, $addOnly)
    {
        $rolesToDissociate = [];

        foreach ($currentRoles as $role) {
            $index = array_search($role, $newRoles, true);

            if ($index === false) {
                $rolesToDissociate[] = $role;
            } else {
                unset($newRoles[$index]);
            }
        }
        if (!$addOnly) {
            foreach ($rolesToDissociate as $roleToDissociate) {
                $this->roleManager->dissociateRole($user, $roleToDissociate);
            }
        }
        $this->roleManager->associateRoles($user, $newRoles);
    }
}
