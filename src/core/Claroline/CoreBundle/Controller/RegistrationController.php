<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\BaseProfileType;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Claroline\CoreBundle\Library\Security\Acl\ClassIdentity;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

/**
 * Controller for user self-registration. Access to this functionality requires
 * that the user is anonymous and the self-registration is allowed by the
 * platform configuration.
 */
class RegistrationController extends Controller
{
    /**
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
     * Registers a new user and displays a flash message in case of success.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerUserAction()
    {
        $this->checkAccess();
        $user = new User();
        $form = $this->get('form.factory')->create(new BaseProfileType(), $user);
        $form->bindRequest($this->get('request'));

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $userRole = $em->getRepository('Claroline\CoreBundle\Entity\Role')
                ->findOneByName(PlatformRoles::USER);
            $user->addRole($userRole);
            $em->persist($user);
            $config = new Configuration();
            $config->setWorkspaceType(Configuration::TYPE_SIMPLE);
            $config->setWorkspaceName('my workspace');
            $wsCreator = $this->get('claroline.workspace.creator');
            $workspace = $wsCreator->createWorkspace($config, $user);
            $workspace->setType(AbstractWorkspace::STANDARD);
            $user->addRole($workspace->getManagerRole());
            $user->setPersonnalWorkspace($workspace);
            $em->persist($workspace);
            $em->flush();

            $msg = $this->get('translator')->trans('account_created', array(), 'platformTranslation');
            $this->getRequest()->getSession()->setFlash('notice', $msg);
        }

        return $this->render(
            'ClarolineCoreBundle:Registration:user_registration_form.html.twig',
            array('form' => $form->createView())
        );
    }

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