<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\HttpFoundation\XmlResponse;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\FacetManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class RegistrationController extends Controller
{
    private $request;
    private $userManager;
    private $configHandler;
    private $validator;
    private $roleManager;
    private $facetManager;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "request"       = @DI\Inject("request"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "facetManager"  = @DI\Inject("claroline.manager.facet_manager"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "validator"     = @DI\Inject("validator"),
     *     "translator"    = @DI\Inject("translator")
     * })
     */
    public function __construct(
        Request $request,
        UserManager $userManager,
        PlatformConfigurationHandler $configHandler,
        ValidatorInterface $validator,
        RoleManager $roleManager,
        FacetManager $facetManager,
        TranslatorInterface $translator
    )
    {
        $this->request = $request;
        $this->userManager = $userManager;
        $this->configHandler = $configHandler;
        $this->validator = $validator;
        $this->roleManager = $roleManager;
        $this->facetManager = $facetManager;
        $this->translator = $translator;
    }
    /**
     * @Route(
     *     "/form",
     *     name="claro_registration_user_registration_form"
     * )
     *
     * @Template()
     *
     * Displays the user self-registration form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userRegistrationFormAction()
    {
        $this->checkAccess();
        $user = new User();
        $localeManager = $this->get('claroline.common.locale_manager');
        $termsOfService = $this->get('claroline.common.terms_of_service_manager');
        $facets = $this->facetManager->findForcedRegistrationFacet();
        $form = $this->get('form.factory')->create(
            new BaseProfileType($localeManager, $termsOfService, $this->translator, $facets),
            $user
        );

        return array('form' => $form->createView());
    }

    /**
     * @Route(
     *     "/create",
     *     name="claro_registration_register_user"
     * )
     *
     * @Template("ClarolineCoreBundle:Registration:userRegistrationForm.html.twig")
     *
     * Registers a new user and displays a flash message in case of success.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerUserAction()
    {
        $this->checkAccess();
        $user = new User();
        $localeManager = $this->get('claroline.common.locale_manager');
        $termsOfService = $this->get('claroline.common.terms_of_service_manager');
        $facets = $this->facetManager->findForcedRegistrationFacet();
        $form = $this->get('form.factory')->create(new BaseProfileType($localeManager, $termsOfService, $this->translator, $facets), $user);
        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $user = $this->get('claroline.manager.user_manager')->createUserWithRole(
                $user,
                PlatformRoles::USER
            );
            $this->roleManager->setRoleToRoleSubject($user, $this->configHandler->getParameter('default_role'));
            //then we adds the differents value for facets.
            foreach ($facets as $facet) {
                foreach ($facet->getPanelFacets() as $panel) {
                    foreach ($panel->getFieldsFacet() as $field) {
                        $this->facetManager->setFieldValue($user, $field, $form->get($field->getName())->getData(), true);
                    }
                }
            }

            $msg = $this->get('translator')->trans('account_created', array(), 'platform');
            $this->get('request')->getSession()->getFlashBag()->add('success', $msg);

            if ($this->configHandler->getParameter('registration_mail_validation')) {
                $msg = $this->get('translator')->trans('please_validate_your_account', array(), 'platform');
                $this->get('request')->getSession()->getFlashBag()->add('success', $msg);
            }

            if ($this->get('claroline.config.platform_config_handler')->getParameter('auto_logging_after_registration')) {
                //this is bad but I don't know any other way (yet)
                $tokenStorage = $this->get('security.token_storage');
                $providerKey = 'main';
                $token = new UsernamePasswordToken($user, $user->getPassword(), $providerKey, $user->getRoles());
                $tokenStorage->setToken($token);
                //a bit hacky I know ~
                return $this->get('claroline.authentication_handler')->onAuthenticationSuccess($this->request, $token);
            }

            return $this->redirect($this->generateUrl('claro_security_login'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/new/user.{format}", name = "claro_register_user")
     * @Method({"POST"})
     */
    public function postUserRegistrationAction($format)
    {
        $formats = array('json', 'xml');

        if (!in_array($format, $formats)) {
            Return new Response(
                "The format {$format} is not supported (supported formats are 'json', 'xml')",
                400
            );
        }

        $status = 200;
        $content = array();

        if ($this->configHandler->getParameter('allow_self_registration')) {
            $request = $this->request;

            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setPlainPassword($request->request->get('password'));
            $user->setFirstName($request->request->get('firstName'));
            $user->setLastName($request->request->get('lastName'));
            $user->setMail($request->request->get('mail'));

            $errorList = $this->validator->validate($user);

            if (count($errorList) > 0) {
                $status = 422;
                foreach ($errorList as $error) {
                    $content[] = array('property' => $error->getPropertyPath(), 'message' => $error->getMessage());
                }
            } else {
                $this->userManager->createUser($user);
            }
        } else {
            $status = 403;
        }

        return $format === 'json' ?
            new JsonResponse($content, $status) :
            new XmlResponse($content, $status);
    }

    /**
     * @Route(
     *     "/activate/{hash}/",
     *     name="claro_security_activate_user",
     *     options={"expose"=true}
     * )
     */
    public function activateUserAction($hash)
    {
        $user = $this->userManager->getByResetPasswordHash($hash);

        if (!$user) {
            throw new \Exception('Hash not found');
        }

        $this->userManager->activateUser($user);
        $this->userManager->logUser($user);

        return new RedirectResponse($this->generateUrl('claro_desktop_open'));
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     * @return Respone
     *
     */
    private function checkAccess()
    {
        $tokenStorage = $this->get('security.token_storage');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $isSelfRegistrationAllowed = $configHandler->getParameter('allow_self_registration');

        if (!$tokenStorage->getToken()->getUser() instanceof User && $isSelfRegistrationAllowed) {
            return;
        }

        throw new AccessDeniedHttpException();
    }
}
