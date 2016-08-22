<?php

namespace FormaLibre\OfficeConnectBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use FormaLibre\OfficeConnectBundle\Library\O365ResponseUser;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 *  @author Nathan Brasseur <nbr@eonix.be>
 */
class OfficeConnectController extends Controller
{
    /** @DI\Inject("formalibre.office_connect.library.authorization_helper_for_graph") */
    private $authHelper;

    /** @DI\Inject("formalibre.office_connect.library.graph_service_access_helper") */
    private $graphHelper;

    /**
     * @EXT\Route(
     *     "/token",
     *     name="claro_o365_get_token"
     * )
     *
     * @return RedirectResponse
     */
    public function getTokenAction()
    {
        $url = $this->authHelper->getAuthorizatonURL();

        return new RedirectResponse($url);
    }

    /**
     * @EXT\Route(
     *     "/login",
     *     name="claro_o365_login"
     * )
     *
     * @return RedirectResponse
     */
    public function loginAction()
    {
        $this->authHelper->GetAuthenticationHeaderFor3LeggedFlow($_GET['code']);
        $jsonResponse = $this->graphHelper->getMeEntry();
        $userResponse = new O365ResponseUser($jsonResponse);
        $userManager = $this->get('claroline.manager.user_manager');
        $email = $userResponse->getEmail();
        $user = $userManager->getUserByEmail($email);
        if ($user === null) {
            $user = new User();
            $user->setFirstName($userResponse->getNickname());
            $user->setLastName($userResponse->getRealName());
            $user->setUsername($userResponse->getUsername());
            $user->setPlainPassword($userResponse->getEmail());
            $user->setMail($userResponse->getEmail());
            $roleName = PlatformRoles::USER;
            $userManager->createUser($user, $roleName);
        }

        $userRepo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:User');
        $securityContext = $this->get('security.context');
        $userLoaded = $userRepo->loadUserByUsername($user->getUsername());
        $providerKey = 'main';
        $token = new UsernamePasswordToken($userLoaded, $userLoaded->getPassword(), $providerKey, $userLoaded->getRoles());
        $securityContext->setToken($token);
        $userManager->logUser($user);

        return new RedirectResponse($this->generateUrl('claro_desktop_open'));
    }
}
