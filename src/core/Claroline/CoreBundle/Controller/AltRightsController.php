<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Workspace\Organizer;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class AltRightsController
{
    private $rightsManager;
    private $formFactory;
    private $request;
    private $sc;
    private $wsOrganizer;
    private $templating;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager"),
     *     "formFactory"   = @DI\Inject("claroline.form.factory"),
     *     "request"       = @DI\Inject("request"),
     *     "sc"            = @DI\Inject("security.context"),
     *     "wsOrganizer"   = @DI\Inject("claroline.workspace.organizer"),
     *     "templating"    = @DI\Inject("templating"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(
        RightsManager $rightsManager,
        FormFactory $formFactory,
        Request $request,
        SecurityContext $sc,
        Organizer $wsOrganizer,
        TwigEngine $templating,
        RoleManager $roleManager
    )
    {
        $this->rightsManager = $rightsManager;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->sc = $sc;
        $this->wsOrganizer = $wsOrganizer;
        $this->templating = $templating;
        $this->roleManager = $roleManager;
    }

    /**
     * @EXT\Route(
     *     "/{resource}/rights/form/role/{role}",
     *     name="claro_resource_right_form",
     *     options={"expose"=true},
     *     defaults={"role"=null}
     * )
     *
     * Displays the resource rights form.
     *
     * @param AbstractResource $resource the resource
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function rightFormAction(AbstractResource $resource, Role $role = null)
    {
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);

        if ($role === null) {
            $rolesRights = $this->rightsManager->getNonAdminRights($resource);
            $datas = $this->wsOrganizer->getDatasForWorkspaceList(true);
            $isDir = ($resource->getResourceType()->getName() === 'directory') ? true: false;

            return $this->templating->renderResponse(
                'ClarolineCoreBundle:Resource:Alt\multipleRightsPage.html.twig',
                array(
                    'resourceRights' => $rolesRights,
                    'resource' => $resource,
                    'isDir' => $isDir,
                    'workspaces' => $datas['workspaces'],
                    'tags' => $datas['tags'],
                    'tagWorkspaces' => $datas['tagWorkspaces'],
                    'hierarchy' => $datas['hierarchy'],
                    'rootTags' => $datas['rootTags'],
                    'displayable' => $datas['displayable'],
                    'workspaceRoles' => $datas['workspaceRoles']
                )
            );
        } else {
            $resourceRights = $this->rightsManager->getOneByRoleAndResource($role, $resource);

            return $this->templating->renderResponse(
                'ClarolineCoreBundle:Resource:Alt\singleRightsForm.html.twig',
                array(
                    'resourceRights' => $resourceRights
                )
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/{resource}/rights/edit",
     *     name="claro_resource_rights_edit",
     *     options={"expose"=true}
     * )
     *
     * Handles the submission of the resource multiple rights form. Expects an array of permissions
     * by role to be passed by POST method. Permissions are set to false when not passed
     * in the request.
     *
     * @param AbstractResource $resource the resource
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function editPermsAction(AbstractResource $resource)
    {
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $datas = $this->getPermissionsFromRequest();

        foreach ($datas as $data) {
            $this->rightsManager->editPerms($data['permissions'], $data['role'], $resource, $isRecursive);
        }

        return new Response("success");
    }

    public function rightsFormCreationPermsAction(AbstractResource $resource, Role $role)
    {

    }

    public function editPermsCreationAction(AbstractResource $resource, Role $role)
    {

    }

    public function getPermissionsFromRequest()
    {
        $permsMap = array('open', 'copy', 'delete', 'edit', 'export');
        $roles = $this->request->request->get('roles');
        $data = array();

        foreach ($roles as $roleId => $perms) {

            foreach ($permsMap as $perm) {
                $changedPerms['can' . ucfirst($perm)] = (array_key_exists($perm, $perms)) ? true: false;
            }

            $data[] = array(
                'role' => $this->roleManager->getRole($roleId),
                'permissions' => $changedPerms
            );
        }

        return $data;
    }

    private function getPermissionsFromForm($form)
    {
        $permissions['canCopy'] = $form->get('canCopy')->getData();
        $permissions['canDelete'] = $form->get('canDelete')->getData();
        $permissions['canOpen'] = $form->get('canOpen')->getData();
        $permissions['canEdit'] = $form->get('canEdit')->getData();
        $permissions['canExport'] = $form->get('canExport')->getData();

        return $form;
    }

    /**
     * Checks if the current user has the right to perform an action on a ResourceCollection.
     * Be careful, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     * @param string                $permission
     * @param ResourceCollection    $collection
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    private function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->sc->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}