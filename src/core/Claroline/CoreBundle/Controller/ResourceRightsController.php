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
     *     "/{resourceId}/rights/form/role/{roleId}",
     *     name="claro_resource_right_form",
     *     options={"expose"=true},
     *     defaults={"roleId"=null}
     * )
     *
     * Displays the resource rights form.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function rightFormAction($resourceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);

        if ($roleId == null) {
            $roleRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findNonAdminRights($resource);
        } else {
            $resourceRight = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findOneBy(array('role' => $roleId, 'resource' => $resourceId));

            if ($resourceRight == null) {
                 $form = $this->createForm(new ResourceRightType($resource), new ResourceRights());

                 return $this->render(
                     'ClarolineCoreBundle:Resource:resource_rights_form_creation.html.twig',
                     array('form' => $form->createView(), 'resourceId' => $resourceId, 'roleId' => $roleId)
                 );
            } else {
                $form = $this->createForm(new ResourceRightType($resource), $resourceRight);

                return $this->render(
                    'ClarolineCoreBundle:Resource:resource_rights_form_edit.html.twig',
                    array('form' => $form->createView(), 'resourceRightId' => $resourceRight->getId())
                );
            }
        }

        $template = $resource->getResourceType()->getName() === 'directory' ?
            'ClarolineCoreBundle:Resource:rights_form_directory.html.twig' :
            'ClarolineCoreBundle:Resource:rights_form_resource.html.twig';

        return $this->render(
            $template,
            array('roleRights' => $roleRights, 'resource' => $resource)
        );
    }

    /**
     * @Route(
     *     "/{resourceId}/rights/edit",
     *     name="claro_resource_rights_edit",
     *     options={"expose"=true}
     * )
     *
     * Handles the submission of the resource rights form. Expects an array of permissions
     * by role to be passed by POST method. Permissions are set to false when not passed
     * in the request.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function editRightsAction($resourceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $rightsRepo = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        $resource = $resourceRepo->find($resourceId);
        $this->checkAccess('EDIT', new ResourceCollection(array($resource)));
        $parameters = $this->get('request')->request->all();
        $permissions = array('open', 'copy', 'delete', 'edit', 'export');
        $editedResourceRightsWithChangeSet = array();

        $finalRights = isset($parameters['isRecursive']) ?
            $this->findChildrenRights($resource):
            $finalRights = $rightsRepo->findNonAdminRights($resource);

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

        return new Response('success');
    }

    /**
     * @Route(
     *     "/{resourceId}/role/{roleId}/right/create",
     *     name="claro_resource_right_create"
     * )
     */
    public function createRightAction($roleId, $resourceId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $resourceRepo->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $role = $em->getRepository('ClarolineCoreBundle:Role')->find($roleId);
        $form = $this->get('form.factory')->create(new ResourceRightType($resource), new ResourceRights());
        $form->bind($request);
        $resourceRights = array();

        if ($form->isValid()) {
            $isRecursive = $form->get('isRecursive')->getData();

            if ($isRecursive) {
                $resourceRights = $this->findAndCreateMissingDescendants($role, $resource);
            } else {
                $resourceRight = new ResourceRights();
                $resourceRight->setRole($role);
                $resourceRight->setResource(
                    $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($resourceId)
                );
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
     *     "/right/{resourceRightId}/edit",
     *     name="claro_resource_right_edit"
     * )
     */
    public function editRightAction($resourceRightId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRight = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')->find($resourceRightId);
        $role = $resourceRight->getRole();
        $resource = $resourceRight->getResource();
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $form = $this->get('form.factory')->create(new ResourceRightType($resource), $resourceRight);
        $form->bind($request);

        if ($form->isValid()) {

            $isRecursive = $form->get('isRecursive')->getData();
            if ($isRecursive) {
                $resourceRights = $this->findAndCreateMissingDescendants($role, $resource);
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
     *     "/{resourceId}/role/{roleId}/right/creation/form",
     *     name="claro_resource_right_creation_form",
     *     options={"expose"=true}
     * )
     *
     * Displays the form for resource creation rights (i.e the right to create a
     * type of resource in a directory). Show the different resource types already
     * allowed for creation.
     *
     * @param integer $resourceId the resource id
     * @param integer $roleId     the role for which the form is displayed
     *
     * @return Response
     *
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function rightCreationFormAction($resourceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->find($roleId);
        $config = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $resourceId, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return $this->render(
            'ClarolineCoreBundle:Resource:rights_creation.html.twig',
            array(
                'configs' => array($config),
                'resourceTypes' => $resourceTypes,
                'resourceId' => $resourceId,
                'roleId' => $roleId
            )
        );
    }

    /**
     * @Route(
     *     "/{resourceId}/role/{roleId}/right/creation/edit",
     *     name="claro_resource_rights_creation_edit",
     *     options={"expose"=true}
     * )
     *
     * Handles the submission of the resource rights creation form. Expects an
     * array of resource type ids to be passed by POST method. Only the types
     * passed in the request will be allowed.
     *
     * @param integer $resourceId the resource id
     * @param integer $roleId     the role for which the form is displayed
     * @return Response
     * @throws AccessDeniedException if the current user is not allowed to edit the resource
     */
    public function editCreationRightsAction($resourceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $resource = $resourceRepo->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $parameters = $this->get('request')->request->all();
        $targetResources = isset($parameters['isRecursive']) ?
            $resourceRepo->findDescendants($resource, true) :
            array($resource);
        $resourceTypeIds = isset($parameters['resourceTypes']) ?
            array_keys($parameters['resourceTypes']) :
            array();

        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findByIds($resourceTypeIds);

        $editedResourceRightsWithChangeSet = $this->setNewCreationTypes($targetResources, $resourceTypes, $roleId);

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
     * @param type $roleId
     *
     * @return array
     */
    private function setNewCreationTypes(array $targetResources, array $resourceTypes, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        foreach ($targetResources as $targetResource) {
            $resourceRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findOneBy(array('resource' => $targetResource, 'role' => $roleId));
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

    private function findAndCreateMissingDescendants(Role $role, AbstractResource $resource)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $alreadyExistings = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findRecursiveByResourceAndRole($resource, $role);
        $descendants = $resourceRepo->findDescendants($resource, true);
        $finalRights = array();

        foreach ($descendants as $descendant) {
            $found = false;
            foreach ($alreadyExistings as $existingRight) {
                if ($existingRight->getResource() === $descendant) {
                    $finalRights[] = $existingRight;
                    $found = true;
                }
            }

            if (!$found) {
                $resourceRight = new ResourceRights();
                $resourceRight->setRole($role);
                $resourceRight->setResource($descendant);
                $finalRights[] = $resourceRight;
            }
        }

        return $finalRights;
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