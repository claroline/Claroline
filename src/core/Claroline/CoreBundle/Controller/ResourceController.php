<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\ExportResourceEvent;

class ResourceController extends Controller
{
    const THUMB_PER_PAGE = 12;

    /**
     * Renders the creation form for a given resource type.
     *
     * @param string  $resourceType
     *
     * @return Response
     */
    public function creationFormAction($resourceType)
    {
        $eventName = $this->get('claroline.resource.utilities')->normalizeEventName('create_form', $resourceType);
        $event = new CreateFormResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return new Response($event->getResponseContent());
    }

    /**
     * Creates a resource and adds an instance of it to the children of an
     * existing instance (e.g. the root folder of a workspace).
     *
     * @param string  $resourceType
     * @param integer $parentInstanceId
     *
     * @return Response
     */
    public function createAction($resourceType, $parentInstanceId)
    {
        $eventName = $this->get('claroline.resource.utilities')->normalizeEventName('create', $resourceType);
        $event = new CreateResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        $response = new Response();

        if (($resource = $event->getResource()) instanceof AbstractResource) {
            $manager = $this->get('claroline.resource.manager');
            if($resourceType === 'file') {
                $files = $this->container->get('request')->files->all();
                $file = $files["file_form"]["name"];
                $mimeType = $file->getClientMimeType();
                $instance = $manager->create($resource, $parentInstanceId, $resourceType, true, $mimeType);
            } else {
                $instance = $manager->create($resource, $parentInstanceId, $resourceType);
            }

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($this->get('claroline.resource.converter')->instanceToJson($instance));
        } else {
            $response->setContent($event->getErrorFormContent());
        }

        return $response;
    }

    /**
     * Removes a resource from a workspace. If the instance is the last to refer to the
     * original instance, the latter will be deleted as well.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function deleteAction($instanceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($instanceId);
        $this->get('claroline.resource.manager')->delete($resourceInstance);

        return new Response('Resource deleted', 204);
    }

    /**
     * Renders the form allowing to edit the base properties (currently the
     * sharable/public/private attribute) of a given resource.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function basePropertiesFormAction($resourceId)
    {
        $resource = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->find($resourceId);

        $user = $this->get('security.context')->getToken()->getUser();

        if ($user != $resource->getCreator()) {
            throw new AccessDeniedHttpException('access denied');
        }

        $form = $this->createForm(new ResourcePropertiesType(), $resource);

        return $this->render(
            'ClarolineCoreBundle:Resource:properties_form.html.twig',
            array('resourceId' => $resourceId, 'form' => $form->createView())
        );
    }

    /**
     * Updates the base properties of a given resource.
     *
     * @param integer $resourceId
     *
     * @return Response
     */
    public function updateBasePropertiesAction($instanceId)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($instanceId);
        $user = $this->get('security.context')->getToken()->getUser();

        if ($user != $resourceInstance->getResource()->getCreator()) {
            throw new AccessDeniedHttpException('access denied');
        }

        $form = $this->createForm(new ResourcePropertiesType(), $resourceInstance->getResource());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $resource = $form->getData();
            $resourceInstance->setResource($resource);
            $em->persist($resource);
            $em->flush();
            $content = $this->renderView(
                'ClarolineCoreBundle:Resource:instances.json.twig',
                array('instances' => array($resourceInstance))
            );
            $response = new Response($content);
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render(
            'ClarolineCoreBundle:Resource:properties_form.html.twig',
            array('instanceId' => $instanceId, 'form' => $form->createView())
        );
    }

    /**
     * Moves a resource instance (changing his parent).
     *
     * @param integer $instanceId
     * @param integer $newParentId
     *
     * @return Response
     */
    public function moveAction($instanceId, $newParentId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $instance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($instanceId);

        if ($instance->getResource()->getShareType() == AbstractResource::PUBLIC_RESOURCE
            || $instance->getResource()->getCreator() == $this->get('security.context')->getToken()->getUser()) {

            $newParent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($newParentId);
            $this->get('claroline.resource.manager')->move($instance, $newParent);

            return new Response('Resource moved', 204);
        }

        throw new AccessDeniedHttpException();
    }

    /**
     * Moves many instances (changes their parents).
     * This function takes an array of parameters wich are the ids of the moved instances.
     *
     * @return Response
     */
    //no verification yet
    public function multiMoveAction($newParentId)
    {
        $ids = $this->container->get('request')->query->all();
        $em = $this->getDoctrine()->getEntityManager();
        $newParent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($newParentId);

        foreach ($ids as $id) {
            $instance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);

            if ($instance != null){
                try {
                     $this->get('claroline.resource.manager')->move($instance, $newParent);
                } catch (\Gedmo\Exception\UnexpectedValueException $e) {
                     return new Response('cannot move a resource into itself', 500);
                }
            }
        }

        return new Response('Resource moved');
    }

    /**
     * Handles any custom action (i.e. not defined in this controller) on a
     * resource of a given type.
     *
     * @param string $resourceType
     * @param string $action
     * @param integer $instanceId
     *
     * @return Response
     */
    public function customAction($resourceType, $action, $resourceId)
    {
        $eventName = $this->get('claroline.resource.utilities')->normalizeEventName($action, $resourceType);
        $event = new CustomActionResourceEvent($resourceId);
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Event '{$eventName}' didn't bring back any response."
            );
        }

        return $event->getResponse();
    }

    /**
     * Download a resource. If it's a directory, its content will be downloaded in an archive.
     * If their are many directories, their id will be sent as as post request. It'll fire an export event.
     *
     * @param integer $instanceId
     *
     * @return Response
     */
    public function exportAction($instanceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $item = $this->get('claroline.resource.exporter')->export($resourceInstance);
        $nameDownload = strtolower(str_replace(' ', '_', $resourceInstance->getName()));
        if ($resourceInstance->getResourceType()->getType() == 'directory') {
            $nameDownload.='.zip';
        }
        $file = file_get_contents($item);
        $response = new Response();
        $response->setContent($file);
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $nameDownload);
        $response->headers->set('Content-Type', 'application/' . pathinfo($item, PATHINFO_EXTENSION));
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * This function takes an array of parameters. Theses parameters are the ids of the instances which are going to be downloaded.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function multiExportAction()
    {
        $ids = $this->container->get('request')->query->all();
        $file = $this->get('claroline.resource.exporter')->multiExport($ids);
        $response = new Response();
        $response->setContent($file);
        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=archive');
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * Returns a json representation of the root resources of every workspace whom
     * the current user is a member.
     *
     * @return Response
     */
    public function rootsAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $results = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getRoots($user);
        $content = $this->get('claroline.resource.converter')->arrayToJson($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the root resource of a workspace.
     *
     * @param integer $workspaceId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rootAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->find($workspaceId);
        $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findOneBy(array('parent' => null, 'workspace' => $workspace->getId()));
        $response = new Response($this->get('claroline.resource.converter')->instanceToJson($root));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the children of a resource instance.
     *
     * @param integer $instanceId
     * @param integer $type
     *
     * @return Response
     */
    public function childrenAction($instanceId, $resourceTypeId)
    {
        if (0 == $instanceId) {
            return new Response('[]');
        }

        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $results = $repo->getChildrenNodes($instanceId, $resourceTypeId);
        $content = $this->get('claroline.resource.converter')->arrayToJson($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of a user instances.
     *
     * @return Response
     */
    public function userEveryInstancesAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $results = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getInstanceList($user);

        $content = $this->get('claroline.resource.converter')->arrayToJson($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the resource types.
     * It doesn't include the directory.
     * Ony listable types are included.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceTypesAction()
    {
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceTypes = $repo->findNavigableResourceTypeWithoutDirectory();

        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:resource_types.json.twig',
            array('resourceTypes' => $resourceTypes)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the resources of a defined type for the current user.
     *
     * @param integer $resourceTypeId
     * @param integer $rootId
     */
    public function resourceListAction($resourceTypeId, $rootId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $resourceType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->find($resourceTypeId);
        $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($rootId);
        $results = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getChildrenInstanceList($root, $resourceType);
        $content = $this->get('claroline.resource.converter')->arrayToJson($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the menus of actions available for each resource type.
     *
     * @return Response
     */
    public function menusAction()
    {
        $repo = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceTypes = $repo->findBy(array('isListable' => 1));
        $pluginResourceTypes = $repo->findListablePluginResourceTypes();

        return $this->render(
            'ClarolineCoreBundle:Resource:resource_menus.json.twig',
            array(
                'resourceTypes' => $resourceTypes,
                'pluginResourceTypes' => $pluginResourceTypes
            )
        );
    }

    /**
     * Adds a resource instance to a workspace.
     *
     * @param integer $instanceId
     * @param integer $instanceDestinationId
     *
     * @return Response
     */
    public function addToWorkspaceAction($instanceId, $instanceDestinationId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceDestinationId);
        $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $this->get('claroline.resource.manager')->addToDirectoryByReference($resource, $parent);
        $em->flush();

        return new Response('success');
    }

    /**
     * Adds multiple resource instance to a workspace.
     *
     * @param integer $instanceDestinationId
     *
     * @return Response
     */
    public function multiAddToWorkspaceAction($instanceDestinationId)
    {
        $ids = $this->container->get('request')->query->all();
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceDestinationId);

        foreach ($ids as $id) {
            $resource = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($id);
            if ($resource != null) {
                $this->get('claroline.resource.manager')->addToDirectoryByReference($resource, $parent);
                $em->flush();
            }
        }

        return new Response('success', 200);
    }

    /**
     * Renders the resource filter
     *
     * @param string $prefix
     *
     * @return Response
     */
    public function rendersFiltersAction($prefix)
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $roots = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getRoots($user);

        $resourceTypes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findNavigableResourceTypeWithoutDirectory();

        return $this->render(
            'ClarolineCoreBundle:Resource:resource_filter.html.twig', array('divPrefix' => $prefix, 'workspaceroots' => $roots, 'resourceTypes' => $resourceTypes)
        );
    }

    /**
     * Renders the resource list as thumbnails.
     *
     * @param integer $parentId
     * @param string $prefix
     *
     * @return Response
     */
    public function rendersResourceThumbnailViewAction($parentId, $prefix)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $breadCrums = null;
        //user root
        if ($parentId == 0) {
            $user = $this->get('security.context')->getToken()->getUser();
            $results = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getRoots($user);
        } else {
            $results = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->getChildrenNodes($parentId);
            $currentFolder = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($parentId);
            $breadCrums = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->parents($currentFolder);
        }

        return $this->render('ClarolineCoreBundle:Resource:resource_thumbnail.html.twig', array('breadCrums' => $breadCrums, 'resources' => $results, 'prefix' => $prefix));
    }

    /**
     * Renders the searched resource list.
     *
     * @return Response
     */
    public function filterAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $compiledArray = $this->get('claroline.resource.searcher')->createSearchArray($this->container->get('request')->query->all());
        $result = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->filter($compiledArray, $user);

        $content = $this->get('claroline.resource.converter')->arrayToJson($result);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns the number of non directory instances for the current user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function countPageInstanceAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $count = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->countInstancesForUser($user);
        $pages = ceil($count/self::THUMB_PER_PAGE);

        return new Response($pages);
    }

    public function rendersPaginatedFlatThumbnailsInstanceAction($page, $prefix)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $results = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getInstanceList($user, $page, self::THUMB_PER_PAGE);
        $currentFolder = null;

         return $this->render('ClarolineCoreBundle:Resource:resource_thumbnail.html.twig',
             array('resources' => $results, 'prefix' => $prefix, 'currentFolder' => $currentFolder));
    }
}