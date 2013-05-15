<?php

namespace Claroline\CoreBundle\Controller;

use \Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Event\LogResourceReadEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ResourceController extends Controller
{
    const THUMB_PER_PAGE = 12;

    /**
     * @Route(
     *     "/form/{resourceType}",
     *     name="claro_resource_creation_form",
     *     options={"expose"=true}
     * )
     *
     * Renders the creation form for a given resource type.
     *
     * @param string $resourceType the resource type
     *
     * @return Response
     */
    public function creationFormAction($resourceType)
    {
        $eventName = 'create_form_'.$resourceType;
        $event = new CreateFormResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getResponseContent() === "") {
            throw new \Exception(
                "Event '{$eventName}' didn't receive any response."
            );
        }

        return new Response($event->getResponseContent());
    }

    /**
     * @Route(
     *     "/create/{resourceType}/{parentId}",
     *     name="claro_resource_create",
     *     options={"expose"=true}
     * )
     *
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
        $this->checkAccess('CREATE', $collection);
        $eventName = 'create_'.$resourceType;
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
     * @Route(
     *     "/open/{resourceType}/{resourceId}",
     *     name="claro_resource_open",
     *     options={"expose"=true}
     * )
     *
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
        $em = $this->getDoctrine()->getManager();
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        $collection = new ResourceCollection(array($resource));
        //If it's a link, the resource will be its target.
        $resource = $this->getResource($resource);
        $this->checkAccess('OPEN', $collection);
        $resource = $this->getResource($resource);
        $event = new OpenResourceEvent($resource);
        $eventName = 'open_'.$resourceType;
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        $resource = $this->getResource($resource);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Open event '{$eventName}' didn't return any Response."
            );
        }

        $resource = $this->getResource($resource);

        $log = new LogResourceReadEvent($resource);
        $this->get('event_dispatcher')->dispatch('log', $log);

        return $event->getResponse();
    }

    /**
     * @Route(
     *     "/delete",
     *     name="claro_resource_delete",
     *     options={"expose"=true}
     * )
     *
     * Removes a many resources from a workspace.
     * Takes an array of ids as parameters (query string: "ids[]=1&ids[]=2" ...).
     *
     * @return Response
     */
    public function deleteAction()
    {
        $ids = $this->container->get('request')->query->get('ids', array());
        $em = $this->getDoctrine()->getManager();
        $collection = new ResourceCollection();

        foreach ($ids as $id) {
            $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->find($id);

            if ($resource != null) {
                $collection->addResource($resource);
            }
        }

        $this->checkAccess('DELETE', $collection);

        foreach ($collection->getResources() as $resource) {
            $this->get('claroline.resource.manager')->delete($resource);
        }

        return new Response('Resource deleted', 204);
    }

    /**
     * @Route(
     *     "/move/{newParentId}",
     *     name="claro_resource_move",
     *     options={"expose"=true}
     * )
     *
     * Moves many resource (changes their parents). This function takes an array
     * of parameters which are the ids of the moved resources
     * (query string: "ids[]=1&ids[]=2" ...).
     *
     * @return Response
     */
    public function moveAction($newParentId)
    {
        $ids = $this->container->get('request')->query->get('ids', array());
        $em = $this->getDoctrine()->getManager();
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $newParent = $resourceRepo->find($newParentId);
        $resourceManager = $this->get('claroline.resource.manager');
        $movedResources = array();
        $collection = new ResourceCollection();

        foreach ($ids as $id) {
            $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->find($id);

            if ($resource !== null) {
                $collection->addResource($resource);
            }
        }

        $collection->addAttribute('parent', $newParent);

        $this->checkAccess('MOVE', $collection);

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
     * @Route(
     *     "/custom/{resourceType}/{action}/{resourceId}",
     *     name="claro_resource_custom",
     *     options={"expose"=true}
     * )
     *
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
        $eventName = $action . '_' . $resourceType;
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
        //$collection = new ResourceCollection(array($resource));

        $event = new CustomActionResourceEvent($resource);
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Custom event '{$eventName}' didn't return any Response."
            );
        }

        //TODO waiting for define CustomActions
        // $logevent = new ResourceLogEvent($ri, $action);
        // $this->get('event_dispatcher')->dispatch('log_resource', $logevent);

        return $event->getResponse();
    }

    /**
     * @Route(
     *     "/export",
     *     name="claro_resource_export",
     *     options={"expose"=true}
     * )
     *
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
                ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->find($id);

            if ($resource != null) {
                $collection->addResource($resource);
            }
        }

        $this->checkAccess('EXPORT', $collection);

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
     * @Route(
     *     "directory/{directoryId}",
     *     name="claro_resource_directory",
     *     options={"expose"=true},
     *     defaults={"resourceId"=0}
     * )
     *
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
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $directoryId = (integer) $directoryId;
        $currentRoles = $this->get('claroline.security.utilities')
            ->getRoles($this->get('security.context')->getToken());

        if ($directoryId === 0) {
            $resources = $resourceRepo->findWorkspaceRootsByUser($user);
        } else {
            $directory = $this->getResource($resourceRepo->find($directoryId));

            if (null === $directory || !$directory instanceof Directory) {
                throw new Exception("Cannot find any directory with id '{$directoryId}'");
            }

            $path = $resourceRepo->findAncestors($directory);
            $resources = $resourceRepo->findChildren($directory, $currentRoles);

            $creationRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findCreationRights($currentRoles, $directory);

            if (count($creationRights) != 0) {
                $translator = $this->get('translator');

                foreach ($creationRights as $type) {
                    $creatableTypes[$type['name']] = $translator->trans($type['name'], array(), 'resource');
                }
            }

            $log = new LogResourceReadEvent($directory);
            $this->get('event_dispatcher')->dispatch('log', $log);
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
     * @Route(
     *     "/copy/{resourceDestinationId}",
     *     name="claro_resource_copy",
     *     options={"expose"=true}
     * )
     *
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
        $em = $this->getDoctrine()->getManager();
        $parent = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceDestinationId);
        $newNodes = array();
        $resources = array();

        foreach ($ids as $id) {
            $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->find($id);

            if ($resource != null) {
                $resources[] = $resource;
            }
        }

        $collection = new ResourceCollection($resources);
        $collection->addAttribute('parent', $parent);

        $this->checkAccess('COPY', $collection);

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
     * @Route(
     *     "/filter/{directoryId}",
     *     name="claro_resource_filter",
     *     options={"expose"=true}
     * )
     *
     * Returns a json representation of a resource search result.
     *
     * @param integer $directoryId The id of the directory from which the search was started
     *
     * @return Response
     */
    public function filterAction($directoryId)
    {
        $queryParameters = $this->container->get('request')->query->all();
        $criteria = $this->get('claroline.resource.manager')->buildSearchArray($queryParameters);
        $criteria['roots'] = isset($criteria['roots']) ? $criteria['roots'] : array();
        $resourceRepo = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $directoryId = (integer) $directoryId;
        $path = array();

        if ($directoryId !== 0) {
            $directory = $this->getResource($resourceRepo->find($directoryId));

            if (null === $directory || !$directory instanceof Directory) {
                throw new Exception("Cannot find any directory with id '{$directoryId}'");
            }

            $path = $resourceRepo->findAncestors($directory);
        }

        $token = $this->get('security.context')->getToken();
        $userRoles = $this->get('claroline.security.utilities')->getRoles($token);
        $resources = $resourceRepo->findByCriteria($criteria, $userRoles);
        $response = new Response(json_encode(array('resources' => $resources, 'path' => $path)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route(
     *     "/shortcut/{newParentId}/create",
     *     name="claro_resource_create_shortcut",
     *     options={"expose"=true}
     * )
     *
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
        $repo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
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

            $this->get('claroline.resource.manager')->cloneParentRights($shortcut->getParent(), $shortcut);
            //$this->get('claroline.resource.manager')->cloneParentRights($shortcut->getParent(), $resource);

            $em->persist($shortcut);
            $em->flush();
            $em->refresh($parent);

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
     * @Route(
     *     "/search/role/code/{code}",
     *     name="claro_resource_find_role_by_code",
     *     options={"expose"=true}
     * )
     */
    public function findRoleByWorkspaceCodeAction($code)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspaceCode($code);
        $arWorkspace = array();
        foreach ($roles as $role) {
            $arWorkspace[$role->getWorkspace()->getCode()][$role->getName()] = array(
                'name' => $role->getName(),
                'translation_key' => $role->getTranslationKey(),
                'id' => $role->getId(),
                'workspace' => $role->getWorkspace()->getName()
            );
        }

        $response = new Response(json_encode($arWorkspace));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function renderBreadcrumbAction($resourceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($resourceId);
        $ancestors = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findAncestors($resource);
        $workspace = $resource->getWorkspace();

        return $this->render(
            'ClarolineCoreBundle:Resource:breadcrumb.html.twig',
            array('ancestors' => $ancestors, 'workspace' => $workspace)
        );
    }

    private function getResource($resource)
    {
        if (get_class($resource) === 'Claroline\CoreBundle\Entity\Resource\ResourceShortcut') {
            $resource = $resource->getResource();
        }

        return $resource;
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
     * @param string $permission
     * @param ResourceCollection $collection
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, ResourceCollection $collection)
    {
        if (!$this->get('security.context')->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}