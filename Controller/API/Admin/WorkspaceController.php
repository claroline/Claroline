<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\API\Admin;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\DiExtraBundle\Annotation as DI;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

/**
 * @NamePrefix("api_")
 */
class WorkspaceController extends FOSRestController
{
    private $formFactory;
    private $om;
    private $request;
    private $roleManager;
    private $templateDir;
    private $tokenStorage;
    private $utilities;
    private $workspaceManager;
    private $workspaceRepo;

    /**
     * @DI\InjectParams({
     *     "formFactory"      = @DI\Inject("form.factory"),
     *     "om"               = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"          = @DI\Inject("request"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "templateDir"      = @DI\Inject("%claroline.param.templates_directory%"),
     *     "tokenStorage"     = @DI\Inject("security.token_storage"),
     *     "utilities"        = @DI\Inject("claroline.utilities.misc"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        ObjectManager $om,
        Request $request,
        RoleManager $roleManager,
        $templateDir,
        TokenStorageInterface $tokenStorage,
        ClaroUtilities $utilities,
        WorkspaceManager $workspaceManager
    )
    {
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->roleManager = $roleManager;
        $this->templateDir = $templateDir;
        $this->tokenStorage = $tokenStorage;
        $this->utilities = $utilities;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceRepo = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace');
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the workspaces list",
     *     views = {"workspace"}
     * )
     */
    public function getWorkspacesAction()
    {
        return $this->workspaceRepo->findAll();
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns a workspace",
     *     views = {"workspace"}
     * )
     */
    public function getWorkspaceAction(Workspace $workspace)
    {
        return $workspace;
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns a workspace with additional datas",
     *     views = {"workspace"}
     * )
     */
    public function getWorkspaceAdditionalDatasAction(Workspace $workspace)
    {
        $datas = array();
        $nbUsers = $this->workspaceManager->countUsers($workspace, true);
        $usedStorage = $this->workspaceManager->getUsedStorage($workspace);
        $nbUsedStorage = $this->utilities->formatFileSize($usedStorage);
        $nbResources = $this->workspaceManager->countResources($workspace);
        $datas['used_storage'] = $nbUsedStorage;
        $datas['nb_users'] = $nbUsers;
        $datas['nb_resources'] = $nbResources;

        return new JsonResponse($datas);
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Returns the non-personal workspaces list",
     *     views = {"workspace"}
     * )
     */
    public function getNonPersonalWorkspacesAction()
    {
        return $this->workspaceRepo->findNonPersonalWorkspaces();
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Create a workspace",
     *     views = {"workspace"},
     *     input="Claroline\CoreBundle\Form\WorkspaceType"
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
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
     * @ApiDoc(
     *     description="Removes a workspace",
     *     views = {"workspace"}
     * )
     */
    public function deleteWorkspaceAction(Workspace $workspace)
    {
        $this->workspaceManager->deleteWorkspace($workspace);

        return array('success');
    }

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update a workspace",
     *     views = {"workspace"},
     *     input="Claroline\CoreBundle\Form\WorkspaceType"
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
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

    /**
     * @View(serializerGroups={"api"})
     * @ApiDoc(
     *     description="Update a workspace owner",
     *     views = {"workspace"}
     * )
     * @ParamConverter("user", class="ClarolineCoreBundle:User", options={"repository_method" = "findForApi"})
     */
    public function putWorkspaceOwnerAction(Workspace $workspace, User $user)
    {
        $currentCreator = $workspace->getCreator();

        if ($currentCreator->getId() !== $user->getId()) {
            $this->om->startFlushSuite();
            $role = $this->roleManager->getManagerRole($workspace);
            $this->roleManager->associateRole($user, $role);
            $this->roleManager->dissociateRole($currentCreator, $role);
            $workspace->setCreator($user);
            $this->workspaceManager->editWorkspace($workspace);
            $this->om->endFlushSuite();
        }

        return $workspace;
    }
}
