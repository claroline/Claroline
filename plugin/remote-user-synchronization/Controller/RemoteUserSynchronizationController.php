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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoteUserSynchronizationController extends Controller
{
    private $authenticator;
    private $request;
    private $roleManager;
    private $session;
    private $tokenManager;
    private $userManager;

    /**
     * @DI\InjectParams({
     *     "authenticator"      = @DI\Inject("claroline.authenticator"),
     *     "requestStack"       = @DI\Inject("request_stack"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "session"            = @DI\Inject("session"),
     *     "tokenManager"       = @DI\Inject("claroline.manager.security_token_manager"),
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager")
     * })
     */
    public function __construct(
        Authenticator $authenticator,
        RequestStack $requestStack,
        RoleManager $roleManager,
        SessionInterface $session,
        SecurityTokenManager $tokenManager,
        UserManager $userManager
    ) {
        $this->authenticator = $authenticator;
        $this->request = $requestStack;
        $this->roleManager = $roleManager;
        $this->session = $session;
        $this->tokenManager = $tokenManager;
        $this->userManager = $userManager;
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
        $firstName = isset($datas['firstName']) ? $datas['firstName'] : null;
        $lastName = isset($datas['lastName']) ? $datas['lastName'] : null;
        $email = isset($datas['email']) ? $datas['email'] : null;
        $password = isset($datas['password']) ? $datas['password'] : null;
        $workspacesTab = isset($datas['workspaces']) ? $datas['workspaces'] : [];
        $userId = isset($datas['userId']) ? $datas['userId'] : null;

        if (!empty($token) && !empty($clientName)) {
            $securityToken = $this->tokenManager
                ->getSecurityTokenByClientNameAndTokenAndIp($clientName, $token, $clientIp);

            if (is_null($securityToken)) {
                $securityToken =
                    $this->tokenManager->getSecurityTokenByClientNameAndTokenAndIp(
                        $clientName,
                        $token,
                        $clientIp.':'.$port
                    );
            }
            if (!is_null($securityToken)) {
                if (!empty($username) && !empty($firstName) && !empty($lastName) && !empty($email) && !empty($password)) {
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
                            $user->setPlainPassword($password);
                            $this->userManager->persistUser($user);
                        } else {
                            $user = new User();
                            $user->setUsername($username);
                            $user->setFirstName($firstName);
                            $user->setLastName($lastName);
                            $user->setMail($email);
                            $user->setPlainPassword($password);
                            $this->userManager->createUser($user);
                            $user->setIsMailValidated(true);
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
                    $this->updateUserRoles($user, $userRoles, $refreshedRoles);
                    $this->authenticator->authenticate($username, $password);

                    return new JsonResponse($user->getId(), 200);
                } else {
                    return new Response('Bad Request', 400);
                }
            }
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
     */
    private function updateUserRoles(User $user, array $currentRoles, array $newRoles)
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

        foreach ($rolesToDissociate as $roleToDissociate) {
            $this->roleManager->dissociateRole($user, $roleToDissociate);
        }
        $this->roleManager->associateRoles($user, $newRoles);
    }
}
