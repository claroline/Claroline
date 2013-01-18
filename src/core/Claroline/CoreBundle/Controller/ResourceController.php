<?php

namespace Claroline\CoreBundle\Controller;

use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\IconType;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Logger\Event\ResourceLogEvent;
use Claroline\CoreBundle\Library\Resource\Event\OpenResourceEvent;

class ResourceController extends Controller
{
    const THUMB_PER_PAGE = 12;

    /**
     * Renders the creation form for a given resource type.
     *
     * @param string $resourceType the resource type
     *
     * @return Response
     */
    public function creationFormAction($resourceType)
    {
        $eventName = $this->get('claroline.resource.utilities')
            ->normalizeEventName('create_form', $resourceType);
        $event = new CreateFormResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return new Response($event->getResponseContent());
    }

    /**
     * Creates a resource.
     *
     * @param string  $resourceType the resource type
     * @param integer $parentId     the parent id
     *
     * @return Response
     */
    public function createAction($resourceType, $parentId)
    {
        $parent = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($parentId);
        $collection = new ResourceCollection(array($parent));
        $collection->setAttributes(array('type' => $resourceType));

        if (!$this->get('security.context')->isGranted('CREATE', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $eventName = $this->get('claroline.resource.utilities')
            ->normalizeEventName('create', $resourceType);
        $event = new CreateResourceEvent($resourceType);
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        $response = new Response();

        if (($resource = $event->getResource()) instanceof AbstractResource) {
            $manager = $this->get('claroline.resource.manager');
            $resource = $manager->create($resource, $parentId, $resourceType);
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent(
                $this->get('claroline.resource.converter')
                    ->toJson($resource, $this->get('security.context')->getToken())
            );
        } else {
            if ($event->getErrorFormContent() != null) {
                $response->setContent($event->getErrorFormContent());
            } else {
                throw new \Exception('creation failed');
            }
        }

        return $response;
    }

    /**
     * Opens a resource.
     *
     * @param integer $resourceId  the resource id
     * @param string $resourceType the resource type
     *
     * @return Response
     *
     * @throws AccessDeniedException
     * @throws \Exception
     */
    public function openAction($resourceId, $resourceType)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        //If it's a link, the resource will be its target.
        $resource = $this->getResource($resource);
        $event = new OpenResourceEvent($resource);
        $eventName = $this->get('claroline.resource.utilities')
            ->normalizeEventName('open', $resourceType);
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Open event '{$eventName}' didn't return any Response."
            );
        }

        $logEvent = new ResourceLogEvent($resource, 'open');
        $this->get('event_dispatcher')->dispatch('log_resource', $logEvent);

        return $event->getResponse();
    }

    /**
     * Removes a many resources from a workspace.
     * Takes an array of ids as parameters (query string: "ids[]=1&ids[]=2" ...).
     *
     * @return Response
     */
    public function deleteAction()
    {
        $ids = $this->container->get('request')->query->get('ids', array());
        $em = $this->getDoctrine()->getEntityManager();
        $collection = new ResourceCollection();

        foreach ($ids as $id) {
            $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                ->find($id);

            if ($resource != null) {
                $collection->addResource($resource);
            }
        }

        if (!$this->get('security.context')->isGranted('DELETE', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        foreach ($collection->getResources() as $resource) {
            $this->get('claroline.resource.manager')->delete($resource);
        }

        return new Response('Resource deleted', 204);
    }

    /**
     * Displays the form allowing to rename a resource.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function renameFormAction($resourceId)
    {
        $resource = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);

        $collection = new ResourceCollection(array($resource));

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $form = $this->createForm(new ResourceNameType(), $resource);

        return $this->render(
            'ClarolineCoreBundle:Resource:rename_form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Renames a resource.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function renameAction($resourceId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $form = $this->createForm(new ResourceNameType(), $resource);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $em->persist($resource);
            $em->flush();
            $response = new Response("[\"{$resource->getName()}\"]");
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render(
            'ClarolineCoreBundle:Resource:rename_form.html.twig',
            array('resourceId' => $resourceId, 'form' => $form->createView())
        );
    }

    /**
     * Displays the resource properties form.
     *
     * @param integer $resourceId the resource id
     *
     * @return Response
     */
    public function propertiesFormAction($resourceId)
    {
        $resource = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $form = $this->createForm(new ResourcePropertiesType(), $resource);

        return $this->render(
            'ClarolineCoreBundle:Resource:form_properties.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Changes the resource properties.
     *
     * @param integer $resourceId the resource id
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function changePropertiesAction($resourceId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $form = $this->createForm(new ResourcePropertiesType(), $resource);
        $form->bindRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $file = $data->getUserIcon();

            if ($file !== null) {
                $this->removeOldIcon($resource);
                $manager = $this->get('claroline.resource.icon_creator');
                $icon = $manager->createCustomIcon($file);
                $em->persist($icon);

                if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                    $icon = $icon->getShortcutIcon();
                }

                $resource->setIcon($icon);
            }

            $resource->setName($data->getName());
            $em->persist($resource);
            $em->flush();
            $content = "{";

            if (isset($icon)) {
                $content .= '"icon": "' . $icon->getRelativeUrl() . '"';
            } else {
                $content .= '"icon": "' . $resource->getIcon()->getRelativeUrl() . '"';
            }

            $content .= ', "name": "' . $resource->getName() . '"';
            $content .= '}';
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render(
            'ClarolineCoreBundle:Resource:form_properties.html.twig',
            array('resourceId' => $resourceId, 'form' => $form->createView())
        );
    }

    /**
     * Moves many resource (changes their parents). This function takes an array
     * of parameters which are the ids of the moved resources
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @return Response
     */
    public function moveAction($newParentId)
    {
        $ids = $this->container->get('request')->query->get('ids', array());
        $em = $this->getDoctrine()->getEntityManager();
        $resourceRepo = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $newParent = $resourceRepo->find($newParentId);
        $resourceManager = $this->get('claroline.resource.manager');
        $movedResources = array();
        $collection = new ResourceCollection();

        foreach ($ids as $id) {
            $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                ->find($id);

            if ($resource !== null) {
                $collection->addResource($resource);
            }
        }

        $collection->addAttribute('parent', $newParent);

        if (!$this->get('security.context')->isGranted('MOVE', $collection)) {
            foreach ($collection->getResources() as $resource) {
                throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
            }
        }

        foreach ($ids as $id) {
            $resource = $resourceRepo->find($id);

            if ($resource != null) {
                try {
                    $movedResource = $resourceManager->move($resource, $newParent);
                    $movedResources[] = $this->get('claroline.resource.converter')->toArray(
                        $movedResource,
                        $this->get('security.context')->getToken()
                    );
                } catch (\Gedmo\Exception\UnexpectedValueException $e) {
                    throw new \RuntimeException('Cannot move a resource into itself');
                }
            }
        }

        $response = new Response(json_encode($movedResources));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Handles any custom action (i.e. not defined in this controller) on a
     * resource of a given type.
     *
     * @param string $resourceType the resource type
     * @param string $action       the action
     * @param integer $resourceId  the resourceId
     *
     * @return Response
     */
    public function customAction($resourceType, $action, $resourceId)
    {
        $eventName = $this->get('claroline.resource.utilities')
            ->normalizeEventName($action, $resourceType);
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));

        if (!$this->get('security.context')->isGranted('OPEN', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $event = new CustomActionResourceEvent($resource);
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Custom event '{$eventName}' didn't return any Response."
            );
        }

        $ri = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);
        $logevent = new ResourceLogEvent($ri, $action);
        $this->get('event_dispatcher')->dispatch('log_resource', $logevent);

        return $event->getResponse();
    }

    /**
     * This function takes an array of parameters. Theses parameters are the ids
     * of the resources which are going to be downloaded
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction()
    {
        $ids = $this->container->get('request')->query->get('ids', array());

        $collection = new ResourceCollection();

        foreach ($ids as $id) {
            $resource = $this->get('doctrine.orm.entity_manager')
                ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                ->find($id);

            if ($resource != null) {
                $collection->addResource($resource);
            }
        }

        if (!$this->get('security.context')->isGranted('EXPORT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        $file = $this->get('claroline.resource.exporter')->exportResources($ids);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * Returns a json representation of the root resource of a workspace.
     *
     * @param integer $workspaceId the workspace id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rootAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->findOneBy(array('parent' => null, 'workspace' => $workspace->getId()));
        $token = $this->get('security.context')->getToken();
        $response = new Response($this->get('claroline.resource.converter')->toJson($root, $token));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of a directory, containing the following items :
     * - The path of the directory
     * - The resource types the user is allowed to create in the directory
     * - The immediate children resources of the directory which are visible for the user
     *
     * If the directory id is '0', a pseudo-directory containing the root directories
     * of the workspaces whose the user is a member is returned.
     * If the directory id is a shortcut id, the directory targeted by the shortcut
     * is returned.
     *
     * @param integer $directoryId the directory id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws Exception if the id doesnt't match any existing directory
     */
    public function openDirectoryAction($directoryId)
    {
        $path = array();
        $creatableTypes = array();
        $resources = array();
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $resourceRepo = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $directoryId = (integer) $directoryId;
        $currentRoles = $this->get('claroline.security.utilities')
            ->getRoles($this->get('security.context')->getToken());

        if ($directoryId === 0) {
            $resources = $resourceRepo->listRootsForUser($user, true);
        } else {
            $directory = $this->getResource($resourceRepo->find($directoryId));

            if (null === $directory || !$directory instanceof Directory) {
                throw new Exception("Cannot find any directory with id '{$directoryId}'");
            }

            $path = $resourceRepo->listAncestors($directory);
            $resources = $resourceRepo->children($directory->getId(), $currentRoles, 0, true, true);

            $creationRights = $em->getRepository('Claroline\CoreBundle\Entity\Rights\ResourceRights')
                ->getCreationRights($currentRoles, $directory);

            if (count($creationRights) != 0) {
                $translator = $this->get('translator');

                foreach ($creationRights as $type) {
                    $creatableTypes[$type['name']] = $translator->trans($type['name'], array(), 'resource');
                }
            }
        }

        $response = new Response(
            json_encode(
                array(
                    'path' => $path,
                    'creatableTypes' => $creatableTypes,
                    'resources' => $resources
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Adds multiple resource resource to a workspace.
     * Needs an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param integer $resourceDestinationId the new parent id.
     *
     * @return Response
     */
    public function copyAction($resourceDestinationId)
    {
        $ids = $this->container->get('request')->query->get('ids', array());
        $token = $this->get('security.context')->getToken();
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceDestinationId);
        $newNodes = array();
        $resources = array();

        foreach ($ids as $id) {
            $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
                ->find($id);

            if ($resource != null) {
                $resources[] = $resource;
            }
        }

        $collection = new ResourceCollection($resources);
        $collection->addAttribute('parent', $parent);

        if (!$this->get('security.context')->isGranted('COPY', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

        foreach ($resources as $resource) {
            $newNode = $this->get('claroline.resource.manager')->copy($resource, $parent);
            $em->persist($newNode);
            $em->flush();
            $em->refresh($parent);
            $newNodes[] = $this->get('claroline.resource.converter')->toArray($newNode, $token);
        }

        $response = new Response(json_encode($newNodes));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of a resource search result.
     *
     * @param integer $directoryId The id of the directory from which the search was started
     *
     * @return Response
     */
    public function filterAction($directoryId)
    {
        $queryParameters = $this->container->get('request')->query->all();
        $allowedStringCriteria = array('name', 'dateFrom', 'dateTo');
        $allowedArrayCriteria = array('roots', 'types');
        $criteria = array();

        foreach ($queryParameters as $parameter => $value) {
            if (in_array($parameter, $allowedStringCriteria) && is_string($value)) {
                $criteria[$parameter] = $value;
            } elseif (in_array($parameter, $allowedArrayCriteria) && is_array($value)) {
                $criteria[$parameter] = $value;
            }
        }

        isset($criteria['roots']) || $criteria['roots'] = array();
        $resourceRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $directoryId = (integer) $directoryId;
        $path = array();

        if ($directoryId !== 0) {
            $directory = $this->getResource($resourceRepo->find($directoryId));

            if (null === $directory || !$directory instanceof Directory) {
                throw new Exception("Cannot find any directory with id '{$directoryId}'");
            }

            $path = $resourceRepo->listAncestors($directory);
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $resources = $resourceRepo->listResourcesForUserWithFilter($criteria, $user, true);
        $response = new Response(
            json_encode(
                array(
                    'resources' => $resources,
                    'path' => $path
                )
            )
        );
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Creates (one or several) shortcuts.
     * Takes an array of ids to be functionnal (query string: "ids[]=1&ids[]=2" ...).
     *
     * @param $newParentId the shortcut parent id
     *
     * @return Response
     */
    public function createShortcutAction($newParentId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $ids = $this->container->get('request')->query->get('ids', array());
        $parent = $repo->find($newParentId);

        foreach ($ids as $resourceId) {
            $resource = $repo->find($resourceId);
            $shortcut = new ResourceShortcut();
            $shortcut->setParent($parent);
            $creator = $this->get('security.context')->getToken()->getUser();
            $shortcut->setCreator($creator);
            $shortcut->setIcon($resource->getIcon()->getShortcutIcon());
            $shortcut->setName($resource->getName());
            $shortcut->setName($this->get('claroline.resource.utilities')->getUniqueName($shortcut, $parent));
            $shortcut->setWorkspace($parent->getWorkspace());
            $shortcut->setResourceType($resource->getResourceType());

            if (get_class($resource) !== 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
                $shortcut->setResource($resource);
            } else {
                $shortcut->setResource($resource->getResource());
            }

            $em->persist($shortcut);
            $em->flush();
            $em->refresh($parent);
            $this->get('claroline.resource.manager')->setResourceRights($shortcut->getParent(), $shortcut);

            $links[] = $this->get('claroline.resource.converter')->toArray(
                $shortcut,
                $this->get('security.context')->getToken()
            );
        }

        $response = new Response(json_encode($links));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

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

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

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

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

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

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

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
        $response->setContent(
            $this->get('claroline.resource.converter')
                ->toJson($resource, $this->get('security.context')->getToken())
        );

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

        if (!$this->get('security.context')->isGranted('EDIT', $collection)) {
            throw new AccessDeniedException(var_dump($collection->getErrorsForDisplay()));
        }

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
     * Removes the icon of a resource from the web/thumbnails folder.
     *
     * @param AbstractResource $resource the resource
     */
    private function removeOldIcon($resource)
    {
        $icon = $resource->getIcon();

        if ($icon->getIconType()->getIconType() == IconType::CUSTOM_ICON) {
            $pathName = $this->container->getParameter('claroline.thumbnails.directory')
                . DIRECTORY_SEPARATOR . $icon->getIconLocation();
            if (file_exists($pathName)) {
                unlink($pathName);
            }
        }
    }

    private function getResource($resource)
    {
        if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $resource = $resource->getResource();
        }

        return $resource;
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
}