<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Form\ResourceRightType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleChangeRightEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ResourceRightsController extends Controller
{
    /**
     * @Route(
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
        $em = $this->get('doctrine.orm.entity_manager');
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);

        if ($role === null) {
            $roleRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findNonAdminRights($resource);
        } else {
            //if a role is specified, display the single role form.
            $resourceRight = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findOneBy(array('role' => $role, 'resource' => $resource));

            if ($resourceRight === null) {
                 $form = $this->createForm(new ResourceRightType($resource), new ResourceRights());

                 return $this->render(
                     'ClarolineCoreBundle:Resource:resource_rights_form_creation.html.twig',
                     array(
                         'form' => $form->createView(),
                         'resource' => $resource,
                         'roleId' => $role->getId(),
                     )
                 );
            } else {
                $form = $this->createForm(new ResourceRightType($resource), $resourceRight);

                return $this->render(
                    'ClarolineCoreBundle:Resource:resource_rights_form_edit.html.twig',
                    array(
                        'form' => $form->createView(),
                        'resourceRightId' => $resourceRight->getId(),
                        'resource' => $resource,
                        'roleId' => $role->getId(),
                    )
                );
            }
        }

        $datas = $this->get('claroline.workspace.organizer')->getDatasForWorkspaceList(true);

        $template = $resource->getResourceType()->getName() === 'directory' ?
            'ClarolineCoreBundle:Resource:rights_form_directory.html.twig' :
            'ClarolineCoreBundle:Resource:rights_form_resource.html.twig';

        return $this->render(
            $template,
            array(
                'roleRights' => $roleRights,
                'resource' => $resource,
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'workspaceRoles' => $datas['workspaceRoles']
            )
        );
    }

    /**
     * @Route(
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
    public function editRightsAction(AbstractResource $resource)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $rightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $this->checkAccess('EDIT', new ResourceCollection(array($resource)));
        $parameters = $this->get('request')->request->all();
        $permissions = array('open', 'copy', 'delete', 'edit', 'export');
        $editedResourceRightsWithChangeSet = array();
        $finalRights = isset($parameters['isRecursive']) ?
            $this->findChildrenRights($resource):
            $rightsRepo->findNonAdminRights($resource);

        foreach ($finalRights as $finalRight) {
            foreach ($permissions as $permission) {
                if ($finalRight->getRole()->getName() !== 'ROLE_ADMIN') {
                    $setter = 'setCan' . ucfirst($permission);
                    $finalRight->{$setter}(isset($parameters['roles'][$finalRight->getRole()->getId()][$permission]));
                    $em->persist($finalRight);
                }
            }

            $unitOfWork = $em->getUnitOfWork();
            $unitOfWork->computeChangeSets();
            $changeSet = $unitOfWork->getEntityChangeSet($finalRight);

            if (count($changeSet) > 0) {
                $editedResourceRightsWithChangeSet[] = array(
                    'resourceRights' => $finalRight, 'changeSet' => $changeSet
                );
            }
        }

        foreach ($editedResourceRightsWithChangeSet as $roleRightWithChangeSet) {
            $roleRight = $roleRightWithChangeSet['resourceRights'];
            $changeSet = $roleRightWithChangeSet['changeSet'];

            $log = new LogWorkspaceRoleChangeRightEvent($roleRight->getRole(), $roleRight->getResource(), $changeSet);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }

        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/{resource}/role/{role}/right/create",
     *     name="claro_resource_right_create"
     * )
     *
     * @param AbstractResource $resource the resource
     * @param Role             $role     the role
     *
     * Handles the submission of the single right form.
     */
    public function createRightAction(Role $role, AbstractResource $resource)
    {
        $request = $this->get('request');
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->get('form.factory')->create(new ResourceRightType($resource), new ResourceRights());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $isRecursive = $form->get('isRecursive')->getData();
            $permissions['canCopy'] = $form->get('canCopy')->getData();
            $permissions['canDelete'] = $form->get('canDelete')->getData();
            $permissions['canOpen'] = $form->get('canOpen')->getData();
            $permissions['canEdit'] = $form->get('canEdit')->getData();
            $permissions['canExport'] = $form->get('canExport')->getData();
            $this->get('claroline.resource.manager')->createRight($permissions, $isRecursive, $role, $resource);

            $isDir = ($resource->getResourceType()->getName() === 'directory') ? true: false;

            return $this->render(
                'ClarolineCoreBundle:Resource:rights_form_row.html.twig',
                array(
                    'canCopy' => $form->get('canCopy')->getData(),
                    'canOpen' => $form->get('canOpen')->getData(),
                    'canDelete' => $form->get('canDelete')->getData(),
                    'canEdit' => $form->get('canEdit')->getData(),
                    'canExport' => $form->get('canExport')->getData(),
                    'isDirectory' => $isDir,
                    'role' => $role,
                    'resource' => $resource
                )
            );
        }
    }

    /**
     * @Route(
     *     "/right/{resourceRight}/edit",
     *     name="claro_resource_right_edit"
     * )
     */
    public function editRightAction(ResourceRights $resourceRight)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $role = $resourceRight->getRole();
        $resource = $resourceRight->getResource();
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->get('form.factory')->create(new ResourceRightType($resource), $resourceRight);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $isRecursive = $form->get('isRecursive')->getData();
            if ($isRecursive) {
                $resourceRights = $this->get('claroline.resource.manager')
                    ->findAndCreateMissingDescendants($role, $resource);
            } else {
                $resourceRights[] = $resourceRight;
            }

            foreach ($resourceRights as $resourceRight) {
                $resourceRight->setCanCopy($form->get('canCopy')->getData());
                $resourceRight->setCanOpen($form->get('canOpen')->getData());
                $resourceRight->setCanDelete($form->get('canDelete')->getData());
                $resourceRight->setCanEdit($form->get('canEdit')->getData());
                $resourceRight->setCanExport($form->get('canExport')->getData());

                $em->persist($resourceRight);
            }

            $em->flush();

            return new Response("success");
        }

    }

    /**
     * @Route(
     *     "/{resource}/role/{role}/right/creation/form",
     *     name="claro_resource_right_creation_form",
     *     options={"expose"=true}
     * )
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
        $em = $this->get('doctrine.orm.entity_manager');
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $config = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $resource, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:rights_creation.html.twig',
            array(
                'configs' => array($config),
                'resourceTypes' => $resourceTypes,
                'resourceId' => $resource->getId(),
                'roleId' => $role->getId()
            )
        );
    }

    /**
     * @Route(
     *     "/{resource}/role/{role}/right/creation/edit",
     *     name="claro_resource_rights_creation_edit",
     *     options={"expose"=true}
     * )
     *
     * Handles the submission of the resource rights creation form. Expects an
     * array of resource type ids to be passed by POST method. Only the types
     * passed in the request will be allowed.
     *
     * @param AbstractResource $resource the resource
     * @param Role             $role     the role for which the form is displayed
     * @return Response
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function editCreationRightsAction(AbstractResource $resource, Role $role)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $parameters = $this->get('request')->request->all();
        $targetResources = isset($parameters['isRecursive']) ?
            $resourceRepo->findDescendants($resource, true, 'directory') :
            array($resource);
        $resourceTypeIds = isset($parameters['resourceTypes']) ?
            array_keys($parameters['resourceTypes']) :
            array();

        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findByIds($resourceTypeIds);

        $editedResourceRightsWithChangeSet = $this->setNewCreationTypes($targetResources, $resourceTypes, $role);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(
            $this->get('claroline.resource.converter')->toJson(
                $resource,
                $this->get('security.context')->getToken()
            )
        );

        foreach ($editedResourceRightsWithChangeSet as $roleRightWithChangeSet) {
            $roleRight = $roleRightWithChangeSet['resourceRights'];
            $changeSet = $roleRightWithChangeSet['changeSet'];

            $log = new LogWorkspaceRoleChangeRightEvent($roleRight->getRole(), $roleRight->getResource(), $changeSet);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }

        return $response;
    }

    private function isInResourceTypes($resourceType, $resourceTypes)
    {
        foreach ($resourceTypes as $current) {
            if ($resourceType->getId() == $current->getId()) {

                return true;
            }
        }

        return false;
    }

    /**
     * Find (and create if missing) resourcerights form every children.
     */
    private function findChildrenRights(AbstractResource $resource)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $rightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $rootRoleRights = $rightsRepo->findNonAdminRights($resource);
        $existingRights = $rightsRepo->findRecursiveByResource($resource);
        $missingRights = array();
        $resources = $resourceRepo->findDescendants($resource, true);
        $roles = array();

        foreach ($rootRoleRights as $rootRoleRight) {
            $roles[] = $rootRoleRight->getRole();
        }

        $toFind = array();

        foreach ($resources as $resource) {
            foreach ($roles as $role) {
                $toFind[] = array('resource' => $resource, 'role' => $role);
            }
        }

        $found = false;
        foreach ($toFind as $item) {
            foreach ($existingRights as $existingRight) {
                if ($item['resource'] == $existingRight->getResource()
                    && $item['role'] == $existingRight->getRole()) {
                    $found = true;
                }
            }
            
            if (!$found) {
                $newRight = new ResourceRights();
                $newRight->setResource($item['resource']);
                $newRight->setRole($item['role']);
                $missingRights[] = $newRight;
            }

            $found = false;
        }

        return array_merge($missingRights, $existingRights);
    }

    /**
     * Sets new creation types to an array of resource for the role $roleId.
     * Returns the changeset.
     *
     * @param array $targetResources
     * @param array $resourceTypes
     * @param Role $role
     *
     * @return array
     */
    private function setNewCreationTypes(array $targetResources, array $resourceTypes, Role $role)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        foreach ($targetResources as $targetResource) {
            $resourceRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findOneBy(array('resource' => $targetResource, 'role' => $role));

            if ($resourceRights == null) {
                $resourceRights = new ResourceRights();
                $resourceRights->setResource($targetResource);
                $resourceRights->setRole($role);
                $resourceRights->setCanCopy(false);
                $resourceRights->setCanDelete(false);
                $resourceRights->setCanEdit(false);
                $resourceRights->setCanExport(false);
                $resourceRights->setCanOpen(false);
                $em->persist($resourceRights);
            }

            $oldResourceTypes = $resourceRights->getCreatableResourceTypes();
            $resourceRights->setCreatableResourceTypes($resourceTypes);
            $addedResourceTypes = $this->findResourceTypesAdded($resourceRights, $oldResourceTypes);
            $removedResourceTypes = $this->findResourceTypesRemoved($resourceRights, $oldResourceTypes);
            $createRights = array();

            if (count($addedResourceTypes) > 0) {
                foreach ($addedResourceTypes as $resourceType) {
                    $createRights[$resourceType->getName()] = array(false, true);
                }
            }

            if (count($removedResourceTypes) > 0) {
                foreach ($removedResourceTypes as $resourceType) {
                    $createRights[$resourceType->getName()] = array(true, false);
                }
            }

            if (count($createRights) > 0) {
                $editedResourceRightsWithChangeSet[] = array(
                    'resourceRights' => $resourceRights,
                    'changeSet' => array('can_create' => $createRights)
                );
            }
        }

        $em->flush();

        return $editedResourceRightsWithChangeSet;
    }

    private function findResourceTypesRemoved($resourceRights, $oldResourceTypes)
    {
        $removedResourceTypes = array();

        foreach ($oldResourceTypes as $resourceType) {
            if (!$this->isInResourceTypes($resourceType, $resourceRights->getCreatableResourceTypes())) {
                $removedResourceTypes[] = $resourceType;
            }
        }

        return $removedResourceTypes;
    }

    private function findResourceTypesAdded($resourceRights, $oldResourceTypes)
    {
        $addedResourceTypes = array();

        foreach ($resourceRights->getCreatableResourceTypes() as $resourceType) {
            if (!$this->isInResourceTypes($resourceType, $oldResourceTypes)) {
                $addedResourceTypes[] = $resourceType;
            }
        }

        return $addedResourceTypes;
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
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}