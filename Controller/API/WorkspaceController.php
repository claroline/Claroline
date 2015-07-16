<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Form\WorkspaceType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Library\Workspace\Configuration;

class WorkspaceController extends FOSRestController
{

    /**
     * @DI\InjectParams({
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager"),
     *     "request"          = @DI\Inject("request"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "templateDir"      = @DI\Inject("%claroline.param.templates_directory%"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        FormFactory      $formFactory,
        WorkspaceManager $workspaceManager,
        ObjectManager    $om,
        Request          $request,
        TokenStorageInterface $tokenStorage,
        $templateDir
    )
    {
        $this->formFactory = $formFactory;
        $this->workspaceManager = $workspaceManager;
        $this->om = $om;
        $this->workspaceRepo = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
        $this->request = $request;
        $this->tokenStorage = $tokenStorage;
        $this->templateDir = $templateDir;
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getWorkspacesAction()
    {
        return $this->workspaceRepo->findAll();
    }

    /**
     * @View(serializerGroups={"api"})
     */
    public function getWorkspaceAction(Workspace $workspace)
    {
        return $workspace;
    }

    /**
     * @View(serializerGroups={"api"})
     * group_form[workspace_form] for the put request
     */
    public function postWorkspaceUserAction(User $user)
    {
        $workspaceType = new WorkspaceType($user);
        $workspaceType->enableApi();
        $form = $this->formFactory->create($workspaceType);
        $form->submit($this->request);
        //$form->handleRequest($this->request);

        if ($form->isValid()) {
            $config = Configuration::fromTemplate(
                $this->templateDir . DIRECTORY_SEPARATOR . 'default.zip'
            );
            $config->setWorkspaceName($form->get('name')->getData());
            $config->setWorkspaceCode($form->get('code')->getData());
            $config->setDisplayable($form->get('displayable')->getData());
            $config->setSelfRegistration($form->get('selfRegistration')->getData());
            $config->setRegistrationValidation($form->get('registrationValidation')->getData());
            $config->setSelfUnregistration($form->get('selfUnregistration')->getData());
            $config->setWorkspaceDescription($form->get('description')->getData());
            $workspace = $this->workspaceManager->create($config, $user);
            $workspace->setEndDate($form->get('endDate')->getData());
            $workspace->setMaxStorageSize($form->get('maxStorageSize')->getData());
            $workspace->setMaxUploadResources($form->get('maxUploadResources')->getData());
            $workspace->setMaxUsers($form->get('maxUsers')->getData());

            $this->workspaceManager->editWorkspace($workspace);

            return $workspace;
        }

        return $form;
    }

    /**
     * @View()
     */
    public function deleteWorkspaceAction(Workspace $workspace)
    {
        $this->workspaceManager->deleteWorkspace($workspace);

        return array('success');
    }

    /**
     * @View(serializerGroups={"api"})
     * group_form[workspace_form] for the put request
     */
    public function putWorkspaceUserAction(Workspace $workspace, User $user)
    {
        $workspaceType = new WorkspaceType($user);
        $workspaceType->enableApi();
        $form = $this->formFactory->create($workspaceType, $workspace);
        $form->submit($this->request);

        if ($form->isValid()) {
            $workspace->setName($form->get('name')->getData());
            $workspace->setCode($form->get('code')->getData());
            $workspace->setDisplayable($form->get('displayable')->getData());
            $workspace->setSelfRegistration($form->get('selfRegistration')->getData());
            $workspace->setRegistrationValidation($form->get('registrationValidation')->getData());
            $workspace->setSelfUnregistration($form->get('selfUnregistration')->getData());
            $workspace->setDescription($form->get('description')->getData());
            $workspace->setEndDate($form->get('endDate')->getData());
            $workspace->setMaxStorageSize($form->get('maxStorageSize')->getData());
            $workspace->setMaxUploadResources($form->get('maxUploadResources')->getData());
            $workspace->setMaxUsers($form->get('maxUsers')->getData());

            $this->workspaceManager->editWorkspace($workspace);

            return $workspace;
        }

        return $form;
    }
}
