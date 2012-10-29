<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Library\Resource\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Library\Logger\Event\ResourceLoggerEvent;

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
        $event = new CreateResourceEvent($resourceType);
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        $response = new Response();

        if (($resource = $event->getResource()) instanceof AbstractResource) {
            $manager = $this->get('claroline.resource.manager');
            if($resourceType === 'file') {
                $mimeType = $resource->getMimeType();
                $instance = $manager->create($resource, $parentInstanceId, $resourceType, true, $mimeType);
            } else {
                $instance = $manager->create($resource, $parentInstanceId, $resourceType);
            }

            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($this->get('claroline.resource.converter')->instanceToJson($instance));
        } else {
            if($event->getErrorFormContent() != null){
                $response->setContent($event->getErrorFormContent());
            } else {
                throw new \Exception('creation failed');
            }
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
     * Removes a many resources from a workspace. If the instance is the last to refer to the
     * original instance, the latter will be deleted as well.
     *
     * @return Response
     */
    public function multiDeleteAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $ids = $this->getRequestParameters();

        foreach ($ids as $id) {
            $resourceInstance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
                ->find($id);
            $this->get('claroline.resource.manager')->delete($resourceInstance);
        }

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
        $ids = $this->getRequestParameters();
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
    public function customAction($resourceType, $action, $instanceId)
    {
        $eventName = $this->get('claroline.resource.utilities')->normalizeEventName($action, $resourceType);
        $event = new CustomActionResourceEvent($instanceId);
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Custom event '{$eventName}' didn't return any Response."
            );
        }

        $ri = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);
        $logevent = new ResourceLoggerEvent(
            $ri,
            ResourceLoggerEvent::CUSTOM_ACTION.'_'.$action
        );
        $this->get('event_dispatcher')->dispatch('log_resource', $logevent);

        return $event->getResponse();
    }

    /**
     * This function takes an array of parameters. Theses parameters are the ids of the instances which are going to be downloaded.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function multiExportAction()
    {
        $ids = $this->getRequestParameters();
        $file = $this->get('claroline.resource.exporter')->exportResourceInstances($ids);
        $response = new StreamedResponse();

        $response->setCallBack(function() use($file){
            readfile($file);
        });

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
        $results = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->listRootsForUser($user, true);
        $content = json_encode($results);
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
        if($instanceId == 0) {
            return $this->rootsAction();
        }
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $results = $repo->listDirectChildrenResourceInstances($instanceId, $resourceTypeId, true);
        $content = json_encode($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the parents of a resource instance.
     *
     * @param integer $instanceId
     *
     * @return Response
     */
    public function parentsAction($instanceId)
    {
        if (0 == $instanceId) {
            return new Response('[]');
        }
        $repo = $parents = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $instance = $repo->find($instanceId);
        $parents = $repo->listAncestors($instance);
        $jsonParents = json_encode($parents);
        $response = new Response($jsonParents);
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
            ->listResourceInstancesForUser($user, true);

        $content = json_encode($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the resource types.
     * It doesn't include the directory.
     * Ony visible types are included.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceTypesAction()
    {
        $repo = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $resourceTypes = $repo->findBy(array('isVisible' => 1));

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
        $results = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->listChildrenResourceInstances($root, $resourceType, true);
        $content = json_encode($results);
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
        $resourceTypes = $repo->findBy(array('isVisible' => 1));

        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:menus.json.twig',
            array(
                'resourceTypes' => $resourceTypes,
            )
        );

        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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
        $ids = $this->getRequestParameters();
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
    public function rendersFiltersAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        $roots = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->listRootsForUser($user, true);

        $resourceTypes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isVisible' => 1));

        $resourceIcons = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->findBy(array('iconType' => 3));

        return $this->render(
            'ClarolineCoreBundle:Resource:resource_filter.html.twig', array('workspaceroots' => $roots, 'resourceTypes' => $resourceTypes, 'resourceIcons' => $resourceIcons)
        );
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
        $results = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->listResourceInstancesForUserWithFilter($compiledArray, $user, true);

        $content = json_encode($results);
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
            ->countResourceInstancesForUser($user);
        $pages = ceil($count/self::THUMB_PER_PAGE);

        return new Response($pages);
    }

    public function paginatedFlatResourceAction($page)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $results = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->listResourceInstancesForUser($user, true, ($page-1)*self::THUMB_PER_PAGE, self::THUMB_PER_PAGE);

        $content = json_encode($results);
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    private function getRequestParameters()
    {
        $params = $this->container->get('request')->query->all();
        unset($params['_']);

        return $params;
    }
}