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
    /**
     * Renders the creation form for a given resource type.
     *
     * @param string  $resourceType
     *
     * @return Response
     */
    public function creationFormAction($resourceType)
    {
        $eventName = $this->get('claroline.resource.manager')->normalizeEventName('create_form', $resourceType);
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
        $eventName = $this->get('claroline.resource.manager')->normalizeEventName('create', $resourceType);
        $event = new CreateResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        $response = new Response();

        if (($resource = $event->getResource()) instanceof AbstractResource) {
            $manager = $this->get('claroline.resource.manager');
            $instance = $manager->create($resource, $parentInstanceId, $resourceType);
            $content = $this->renderView(
                'ClarolineCoreBundle:Resource:instances.json.twig',
                array('instances' => array($instance))
            );
            $response->headers->set('Content-Type', 'application/json');
            $response->setContent($content);
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
        $eventName = $this->get('claroline.resource.manager')->normalizeEventName($action, $resourceType);
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
        $item = $this->get('claroline.resource.manager')->export($resourceInstance);
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
     * It also needs the "displayMode".
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function multiExportAction($type)
    {
        $file = $this->get('claroline.resource.manager')->multiExport($type);
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

        $workspaces = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')
            ->getAllWsOfUser($user);
        $roots = array();

        // TODO : use a one-to-one relationship between workspace and root directory
        foreach ($workspaces as $workspace) {
            $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
                ->findOneBy(array('parent' => null, 'workspace' => $workspace->getId()));
            $roots[] = $root;
        }

        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:instances.json.twig',
            array('instances' => $roots)
        );
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
        $roots = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findBy(array('parent' => null, 'workspace' => $workspace->getId()));
        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:instances.json.twig',
            array('instances' => $roots)
        );
        $response = new Response($content);
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
        $parent = $repo->find($instanceId);
        $results = $repo->getChildrenNodes($parent, $resourceTypeId);
        $content = $this->generateDynatreeJsonFromSql($results);
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
        $content = $this->generateDynatreeJsonFromSql($results);
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
     * Adds a resource instance to a workspace. Options must be must be 'ref' or 'copy'.
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

    public function accessibilityManagerAction($parentId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $parent = null;
        if ($parentId == 0) {
            $user = $this->get('security.context')->getToken()->getUser();
            $workspaces = $em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')->getAllWsOfUser($user);
            $instances = array();

            foreach ($workspaces as $workspace) {
                $instance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
                    ->findOneBy(array('parent' => null, 'workspace' => $workspace->getId()));
                $instances[] = $instance;
            }

        } else {
            $repo = $this->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
            $parent = $repo->find($parentId);
            $instances = $repo->getListableChildren($parent, 0);
        }

        $resourceTypes = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findBy(array('isListable' => 1));

        return $this->render('ClarolineCoreBundle:Resource:accessibility_manager.html.twig', array('instances' => $instances, 'parent' => $parent, 'resourceTypes' => $resourceTypes));
    }

    public function accessibilityFormCreationAction($resourceType, $parentId)
    {
        $eventName = $this->get('claroline.resource.manager')->normalizeEventName('create_form', $resourceType);
        $event = new CreateFormResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return $this->render('ClarolineCoreBundle:Resource:accessibility_form.html.twig', array('form' => $event->getResponseContent(), 'parentId' => $parentId, 'resourceType' => $resourceType));
    }

    //performance test function
    public function getDataTreeAction($rootId) {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $resourceType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->find(1);
        $root = $repo->find($rootId);
        $results = $repo->getChildrenInstanceList($root, $resourceType);
        $json = $this->generateDynatreeJsonFromSql($results);

        return new Response(var_dump($json));
    }

    private function generateDynatreeJsonFromSql($results)
    {
        $json = "[";
        $i = 0;
        foreach ($results as $key => $item){
            $stringitem ='';
            if($i != 0){
                $stringitem.=",";
            } else {
                $i++;
            }
            $stringitem.= '{';
            $stringitem.= ' "title": "'.$item['name'].'", ';
            $stringitem.= ' "key": "'.$item['id'].'", ';
            $stringitem.= ' "instanceId": "'.$item['id'].'", ';
            $stringitem.= ' "resourceId": "'.$item['resource_id'].'", ';
            $stringitem.= ' "type": "'.$item['type'].'", ';
            $stringitem.= ' "typeId": "'.$item['resource_type_id'].'", ';
            $stringitem.= ' "workspaceId": "'.$item['workspace_id'].'", ';
            $stringitem.= ' "dateInstanceCreation": "'.$item['created'].'" ';
            if ($item['icon'] != null ){
                $stringitem.= ' ", icon": "'.$item['icon'].'" ';
            }
            if ($item['is_navigable'] != 0) {
                $stringitem.=', "isFolder": true ';
                $stringitem.=', "isLazy": true ';
            }
            $stringitem.='}';
            $json.=$stringitem;
        }

        $json.="]";

        return $json;
    }
}