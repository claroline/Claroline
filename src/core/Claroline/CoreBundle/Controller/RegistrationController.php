<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Security\Acl\ClassIdentity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class RegistrationController extends Controller
{
    /**
     * @Route(
     *     "/form",
     *     name="claro_registration_user_registration_form"
     * )
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

        return $this->render(
            'ClarolineCoreBundle:Registration:user_registration_form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/create",
     *     name="claro_registration_register_user"
     * )
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
        $form->bind($this->get('request'));

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $userRole = $em->getRepository('ClarolineCoreBundle:Role')
                ->findOneByName(PlatformRoles::USER);
            $user->addRole($userRole);
            $user = $this->container->get('claroline.user.creator')->create($user);

            $msg = $this->get('translator')->trans('account_created', array(), 'platform');
            $this->getRequest()->getSession()->getFlashBag()->add('success', $msg);
        }

        return $this->render(
            'ClarolineCoreBundle:Registration:user_registration_form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Checks if a user is allowed to register.
     * ie: if the self registration is disabled, he can't.
     *
     * @return Respone
     *
     * @throws AccessDeniedHttpException
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

        throw new AccessDeniedHttpException();
    }
}