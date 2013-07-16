<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ResourceRightsController
{
    private $rightsManager;
    private $request;
    private $sc;
    private $wsTagManager;
    private $templating;
    private $roleManager;
    private $om;

    /**
     * @DI\InjectParams({
     *     "rightsManager" = @DI\Inject("claroline.manager.rights_manager"),
     *     "request"       = @DI\Inject("request"),
     *     "sc"            = @DI\Inject("security.context"),
     *     "wsTagManager"  = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "templating"    = @DI\Inject("templating"),
     *     "roleManager"   = @DI\Inject("claroline.manager.role_manager"),
     *     "om"            = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        RightsManager $rightsManager,
        Request $request,
        SecurityContext $sc,
        WorkspaceTagManager $wsTagManager,
        TwigEngine $templating,
        RoleManager $roleManager,
        ObjectManager $om
    )
    {
        $this->rightsManager = $rightsManager;
        $this->request = $request;
        $this->sc = $sc;
        $this->wsTagManager = $wsTagManager;
        $this->templating = $templating;
        $this->roleManager = $roleManager;
        $this->om = $om;
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
        $isDir = ($resource->getResourceType()->getName() === 'directory') ? true: false;

        if ($role === null) {
            $rolesRights = $this->rightsManager->getNonAdminRights($resource);
            $datas = $this->wsTagManager->getDatasForWorkspaceList(true);

            return $this->templating->renderResponse(
                'ClarolineCoreBundle:Resource:multipleRightsPage.html.twig',
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
                'ClarolineCoreBundle:Resource:singleRightsForm.html.twig',
                array(
                    'resourceRights' => $resourceRights,
                    'isDir' => $isDir
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
        $isRecursive = $this->request->request->get('isRecursive');

        foreach ($datas as $data) {
            $this->rightsManager->editPerms($data['permissions'], $data['role'], $resource, $isRecursive);
        }

        return new Response("success");
    }

    /**
     * @EXT\Route(
     *     "/{resource}/role/{role}/right/creation/form",
     *     name="claro_resource_right_creation_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Resource:rightsCreation.html.twig")
     *
     * Displays the form for resource creation rights (i.e the right to create a
     * type of resource in a directory). Show the different resource types already
     * allowed for creation.
     *
     * @param AbstractResource $resource the resource
     * @param Role $role                 the role for which the form is displayed
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function rightCreationFormAction(AbstractResource $resource, Role $role)
    {
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);

        return array(
            'configs' => array($this->rightsManager->getOneByRoleAndResource($role, $resource)),
            'resourceTypes' => $this->rightsManager->getResourceTypes(),
            'resourceId' => $resource->getId(),
            'roleId' => $role->getId()
        );
    }

    /**
     * @EXT\Route(
     *     "/{resource}/role/{role}/right/creation/edit",
     *     name="claro_resource_rights_creation_edit",
     *     options={"expose"=true}
     * )
     *
     * Handles the submission of the resource rights creation form. Expects an
     * array of resource type ids to be passed by POST method. Only the types
     * passed in the request will be allowed.
     *
     * @param AbstractResource $resource      the resource
     * @param Role             $role          the role for which the form is displayed
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function editPermsCreationAction(AbstractResource $resource, Role $role)
    {
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $isRecursive = $this->request->request->get('isRecursive');
        $ids = $this->request->request->get('resourceTypes');
        $resourceTypes = $ids === null ?
            array() :
            $this->om->findByIds('ClarolineCoreBundle:Resource\ResourceType', array_keys($ids));
        $this->rightsManager->editCreationRights($resourceTypes, $role, $resource, $isRecursive);

        return new Response("success");
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