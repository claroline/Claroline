<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ResourceRightsController extends Controller
{
    /**
     * Displays the resource rights form.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function rightFormAction($resourceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $configs = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findBy(array('resource' => $resource));

        if ($resource->getResourceType()->getName() == 'directory') {
            return $this->render(
                'ClarolineCoreBundle:Resource:rights_form_directory.html.twig',
                array('configs' => $configs, 'resource' => $resource)
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Resource:rights_form_resource.html.twig',
            array('configs' => $configs, 'resource' => $resource)
        );
    }

    /**
     * Displays the resource rights creation form. This is only usefull for directories.
     * It'll show the different resource types already registered.
     *
     * @param integer $resourceId the resource id
     * @param integer $roleId     the role for which the form is displayed
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function rightCreationFormAction($resourceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->find($roleId);
        $config = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findOneBy(array('resource' => $resourceId, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

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
     * Handles the submission of the resource rights creation Form
     * @param type $resourceId the resource id
     * @param type $roleId     the role for which the form is displayed
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws AccessDeniedException
     */
    public function editCreationRightsAction($resourceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $request = $this->get('request');
        $array = $request->request->all();

        if (isset($array['isRecursive'])) {
            $isRecursive = true;
            unset($array['isRecursive']);
        } else {
            $isRecursive = false;
        }

        $keys = array_keys($array);

        foreach ($keys as $key) {
            $split = explode('-', $key);
            $resourceTypesIds[] = $split[1];
        }

        if (isset($resourceTypesIds)) {
            $this->setCreationPermissionForResource($resourceId, $resourceTypesIds, $roleId);

            if ($isRecursive) {
                $dirType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                    ->findOneBy(array('name' => 'directory'));
                $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                    ->getDescendant($resource, $dirType);

                foreach ($resources as $resource) {
                    $this->setCreationPermissionForResource($resources, $resourceTypesIds, $roleId);
                }
            }
        } else {
            $this->resetCreationPermissionForResource($resourceId, $roleId);

            if ($isRecursive) {
                $dirType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                    ->findOneBy(array('name' => 'directory'));
                $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                    ->getDescendant($resource, $dirType);

                foreach ($resources as $resource) {
                    $this->resetCreationPermissionForResource($resources, $roleId);
                }
            }
        }

        $em->flush();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($this->get('claroline.resource.converter')
            ->toJson($resource, $this->get('security.context')->getToken()));

        return $response;
    }

    /**
     * Handles  the submission of the resource rights form
     *
     * @param type $resourceId the resource id
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * @throws AccessDeniedException
     */
    public function editRightsAction($resourceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        $this->checkAccess('EDIT', $collection);
        $parameters = $this->get('request')->request->all();

        if (isset($parameters['isRecursive'])) {
            $isRecursive = true;
            unset($parameters['isRecursive']);
        } else {
            $isRecursive = false;
        }

        $checks = $this->get('claroline.security.utilities')
            ->setRightsRequest($parameters, 'resource');
        $configs = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findBy(array('resource' => $resource));

        foreach ($configs as $config) {
            if (isset($checks[$config->getId()])) {
                $config->setRights($checks[$config->getId()]);
            } else {
                $config->reset();
            }
            $em->persist($config);
        }

        if ($isRecursive) {
            $resources = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                ->getDescendant($resource);

            foreach ($resources as $resource) {
                $configs = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
                    ->findBy(array('resource' => $resource));

                foreach ($configs as $config) {
                    $key = $this->findKeyForConfig($checks, $config);

                    if ($key !== null) {
                        $config->setRights($checks[$key]);
                    } else {
                        $config->reset();
                    }

                    $em->persist($config);
                }
            }
        }

        $em->flush();

        // TODO : send the new rights to the manager.js ?
        // $json = $resource;

        return new Response('success');
    }

    /**
     * Find the correct key to use in the $checks array for a ResourceRights entity.
     *
     * @param array $checks
     * @param ResourceRight $config
     *
     * @return null|integer
     */
    private function findKeyForConfig($checks, $config)
    {
        $keys = array_keys($checks);
        foreach ($keys as $key) {
            $baseConfig = $this->get('doctrine.orm.entity_manager')
                ->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
                ->find($key);
            $role = $baseConfig->getRole();

            if ($config->getRole() == $role) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Sets the resource creation permission for a resource and a role.
     *
     * @param integer|AbstractResource $resource
     * @param array $resourceTypesIds
     * @param integer|Role $role
     */
    private function setCreationPermissionForResource($resourceId, $resourceTypesIds, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $config = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findOneBy(array('resource' => $resourceId, 'role' => $roleId));
        $config->cleanResourceTypes();

        foreach ($resourceTypesIds as $id) {
            $rt = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->find($id);
            $config->addResourceType($rt);
        }

        $em->persist($config);
    }

    /**
     * Resets the creation permission for a resource and a role.
     *
     * @param integer|AbstractResource $resource
     * @param integer|Role $role
     */
    private function resetCreationPermissionForResource($resourceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $config = $em->getRepository('ClarolineCoreBundle:Rights\ResourceRights')
            ->findOneBy(array('resource' => $resourceId, 'role' => $roleId));
        $config->cleanResourceTypes();
        $em->persist($config);
    }

    /**
     * Checks if the current user has the right to do an action on a ResourceCollection.
     * Be carrefull, ResourceCollection may need some aditionnal parameters.
     *
     * - for CREATE: $collection->setAttributes(array('type' => $resourceType))
     *  where $resourceType is the name of the resource type.
     * - for MOVE / COPY $collection->setAttributes(array('parent' => $parent))
     *  where $parent is the new parent entity.
     *
     *
     * @param string $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, $collection)
    {
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }
    }
}
