<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Library\Security\Acl\ClassIdentity;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\DiExtraBundle\Annotation as DI;

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

    /**
     * @DI\InjectParams({
     *     "request"       = @DI\Inject("request"),
     *     "userManager"   = @DI\Inject("claroline.manager.user_manager"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "validator"     = @DI\Inject("validator")
     * })
     */
    public function __construct(
        Request $request,
        UserManager $userManager,
        PlatformConfigurationHandler $configHandler,
        ValidatorInterface $validator
    )
    {
        $this->request = $request;
        $this->userManager = $userManager;
        $this->configHandler = $configHandler;
        $this->validator = $validator;
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
        $form = $this->get('form.factory')->create(new BaseProfileType(), $user);

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

        $form = $this->get('form.factory')->create(new BaseProfileType(), $user);

        $form->handleRequest($this->get('request'));

        if ($form->isValid()) {
            $this->get('claroline.manager.user_manager')->createUserWithRole(
                $user,
                PlatformRoles::USER
            );
            $msg = $this->get('translator')->trans('account_created', array(), 'platform');
            $this->getRequest()->getSession()->getFlashBag()->add('success', $msg);
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/new", name = "claro_register_user")
     * @Method({"POST"})
     */
    public function postUserRegistrationAction()
    {
        $status = 500;
        $content = array();

        if ($this->configHandler->getParameter('allow_self_registration')) {
            $request = $this->request;

            $user = new User();
            $user->setUsername($request->request->get('username'));
            $user->setPlainPassword($request->request->get('password'));
            $user->setFirstName($request->request->get('firstname'));
            $user->setLastName($request->request->get('lastname'));
            $user->setMail($request->request->get('mail'));

            $errorList = $this->validator->validate($user);

            if (count($errorList) > 0) {
                $status = 422;
                foreach ($errorList as $error) {
                    $content[] = array('property' => $error->getPropertyPath(), 'message' => $error->getMessage());
                }
            } else {
                $this->userManager->createUser($user);
                $status = 200;
            }
        } else {
            $status = 403;
        }

        $response = new JsonResponse($content);
        $response->setStatusCode($status);

        return $response;
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @return Respone
     *
     * @throws AccessDeniedException
     */
    private function checkAccess()
    {
        $securityContext = $this->get('security.context');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $isSelfRegistrationAllowed = $configHandler->getParameter('allow_self_registration');

        if (!$securityContext->getToken()->getUser() instanceof User && $isSelfRegistrationAllowed) {
            return;
        }

        if ($securityContext->isGranted('CREATE', ClassIdentity::fromDomainClass('Claroline\CoreBundle\Entity\User'))) {
            return;
        }

        throw new AccessDeniedException();
    }
}
