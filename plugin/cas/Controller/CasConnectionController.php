<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/15/17
 */

namespace Claroline\CasBundle\Controller;

use Claroline\CasBundle\Library\Configuration\CasServerConfigurationFactory;
use Claroline\CasBundle\Library\Sso\CasFactory;
use Claroline\CasBundle\Manager\CasManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Class CasConnexionController.
 */
class CasConnectionController extends Controller
{
    /**
     * @var CasManager
     * @DI\Inject("claroline.manager.cas_manager")
     */
    private $casManager;

    /**
     * @var CasFactory
     * @DI\Inject("be_simple.sso_auth.factory")
     */
    private $ssoFactory;

    /**
     * @var PlatformConfigurationHandler
     * @DI\Inject("claroline.config.platform_config_handler")
     */
    private $platformConfigHandler;

    /**
     * @var CasServerConfigurationFactory
     * @DI\Inject("claroline.factory.cas_configuration")
     */
    private $casServerConfigFactory;

    /**
     * @EXT\Route("/login", name="claro_cas_login")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        $request->getSession()->set('LOGGED_VIA_CAS', true);
        $manager = $this->ssoFactory->getManager('cas_sso');

        return new RedirectResponse($manager->getServer()->getLoginUrl());
    }

    /**
     * @EXT\Route("/enter", name="claro_cas_security_entry_point")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @SEC\PreAuthorize("hasRole('ROLE_USER')")
     */
    public function enterAction()
    {
        return $this->redirectToRoute('claro_desktop_open');
    }

    /**
     * @EXT\Route("/login/failure", name="claro_cas_login_failure")
     * @EXT\Template("ClarolineCasBundle:Connect:check_connexion.html.twig")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginCasFailureAction(Request $request)
    {
        $casLogin = $request->getSession()->get('CAS_AUTHENTICATION_USER_ID');
        if (empty($casLogin)) {
            return $this->redirectToRoute('claro_index');
        }

        $selfRegistration = $this->platformConfigHandler->getParameter('allow_self_registration');
        $user = $this->get('claroline.manager.user_manager')->getUserByUsername($casLogin);

        return [
            'casLogin' => $casLogin,
            'selfRegistration' => $selfRegistration,
            'claroUser' => $user,
            'serviceName' => $this->casServerConfigFactory->getCasConfiguration()->getName(),
        ];
    }

    /**
     * @EXT\Route("/register", name="claro_cas_register")
     * @EXT\Template("ClarolineCasBundle:Connect:create_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function registerAction(Request $request)
    {
        $selfRegistration = $this->platformConfigHandler->getParameter('allow_self_registration');
        $casLogin = $request->getSession()->get('CAS_AUTHENTICATION_USER_ID');
        if (empty($casLogin) || !$selfRegistration) {
            return $this->redirectToRoute('claro_index');
        }
        $user = new User();
        if ($this->isValidMail($casLogin)) {
            $user->setMail($casLogin);
        } else {
            $user->setUsername($casLogin);
        }

        $form = $this->casManager->getRegistrationForm($user);

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route("/create_account", name="claro_cas_create_account")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCasBundle:Connect:create_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function createAccountAction(Request $request)
    {
        $selfRegistration = $this->platformConfigHandler->getParameter('allow_self_registration');
        $casLogin = $request->getSession()->get('CAS_AUTHENTICATION_USER_ID');
        if (empty($casLogin) || !$selfRegistration) {
            return $this->redirectToRoute('claro_index');
        }

        return $this->casManager->createNewAccount($request, $casLogin);
    }

    /**
     * @EXT\Route("/link/login", name="claro_cas_login_link")
     * @EXT\Template("ClarolineCasBundle:Connect:link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function loginLinkAction(Request $request)
    {
        $casLogin = $request->getSession()->get('CAS_AUTHENTICATION_USER_ID');
        if (empty($casLogin)) {
            return $this->redirectToRoute('claro_index');
        }
        $request->getSession()->set('LOGGED_VIA_CAS', true);

        return [];
    }

    /**
     * @EXT\Route("/link_account", name="claro_cas_link_account")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCasBundle:Connect:link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function linkAccountAction(Request $request)
    {
        $casLogin = $request->getSession()->get('CAS_AUTHENTICATION_USER_ID');
        if (empty($casLogin)) {
            return $this->redirectToRoute('claro_index');
        }
        $request->getSession()->set('LOGGED_VIA_CAS', true);

        return $this->casManager->linkAccount($request, $casLogin);
    }

    /**
     * @EXT\Route("/link_account_mail", name="claro_cas_link_account_mail")
     * @EXT\Method("GET")
     * @EXT\Template("ClarolineCasBundle:Connect:link_account.html.twig")
     *
     * @param Request $request
     *
     * @return array
     */
    public function linkAccountByMailAction(Request $request)
    {
        $casLogin = $request->getSession()->get('CAS_AUTHENTICATION_USER_ID');
        if (empty($casLogin)) {
            return $this->redirectToRoute('claro_index');
        }
        $request->getSession()->set('LOGGED_VIA_CAS', true);

        return $this->casManager->linkAccount($request, $casLogin, $casLogin);
    }

    private function isValidMail($mail)
    {
        $errors = $this->get('validator')->validate($mail, [new Email()]);

        return count($errors) === 0;
    }
}
