<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceInstance;
use Claroline\CoreBundle\Form\ResourcePropertiesType;
use Claroline\CoreBundle\Library\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Library\Resource\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Library\Resource\CustomActionResourceEvent;

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
        $eventName = $this->normalizeEventName('create_form', $resourceType);
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
        $eventName = $this->normalizeEventName('create', $resourceType);
        $event = new CreateResourceEvent();
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        $response = new Response();

        if (($resource = $event->getResource()) instanceof AbstractResource) {
            $manager = $this->get('claroline.resource.manager');
            $instance = $manager->create($resource, $parentInstanceId, $resourceType);
            $content = $this->renderView(
                'ClarolineCoreBundle:Resource:resources.json.twig',
                array('resources' => array($instance))
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

        if (1 === $resourceInstance->getResource()->getInstanceCount()) {
            if ($resourceInstance->getResourceType()->getType() !== 'directory') {
                $eventName = $this->normalizeEventName(
                    'delete',
                    $resourceInstance->getResourceType()->getType()
                );
                $event = new DeleteResourceEvent(array($resourceInstance->getResource()));
                $this->get('event_dispatcher')->dispatch($eventName, $event);
            } else {
                $this->deleteDirectory($resourceInstance);
            }
        }

        $resourceInstance->getResource()->removeResourceInstance($resourceInstance);
        $em->remove($resourceInstance);
        $em->flush();

        return new Response('Resource deleted', 204);
    }

    /**
     * Renders the form allowing to edit the base properties (currently only the name and the
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
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($resourceId);
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
        $resourceInstance = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')
            ->find($instanceId);
        $form = $this->createForm(new ResourcePropertiesType(), $resourceInstance->getResource());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $resource = $form->getData();
            $resourceInstance->setResource($resource);
            $em->persist($resource);
            $em->flush();
            $content = $this->renderView(
                'ClarolineCoreBundle:Resource:resources.json.twig',
                array('resources' => array($resourceInstance))
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
        $newParent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($newParentId);
        $this->get('claroline.resource.manager')->move($instance, $newParent);

        return new Response('Resource moved', 204);
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
        $instance = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->find($instanceId);
        $eventName = $this->normalizeEventName($action, $resourceType);
        $event = new CustomActionResourceEvent($instance->getResource()->getId());
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (!$event->getResponse() instanceof Response) {
            throw new \Exception(
                "Event '{$eventName}' didn't bring back any response."
            );
        }

        return $event->getResponse();
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
            $root = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->findOneBy(array('parent' => null, 'workspace' => $workspace->getId()));
            $roots[] = $root;
        }

        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:resources.json.twig',
            array('resources' => $roots)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a json representation of the children of a resource instance.
     *
     * @param integer $instanceId
     *
     * @return Response
     */
    public function childrenAction($instanceId)
    {
        $repo = $this->getDoctrine()
            ->getEntityManager()
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance');
        $parent = $repo->find($instanceId);
        $resourceInstances = $repo->getListableChildren($parent);
        $content = $this->renderView(
            'ClarolineCoreBundle:Resource:resources.json.twig',
            array('resources' => $resourceInstances)
        );
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
        $resourceTypes = $repo->findAll();
        $pluginResourceTypes = $repo->findPluginResourceTypes();

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
     * @param integer $resourceId
     * @param string  $options
     * @param integer $instanceDestinationId
     *
     * @return Response
     */
    public function addToWorkspaceAction($instanceId, $options, $instanceDestinationId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $parent = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceDestinationId);
        $instance = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->find($instanceId);

        if ($options == 'ref') {
            $this->addToDirectoryByReference($instance, $parent);
        } else {
            $this->addToDirectoryByCopy($instance, $parent);
        }

        $em->flush();
        return new Response('success');
    }

    private function normalizeEventName($prefix, $resourceType)
    {
        return $prefix . '_' . strtolower(str_replace(' ', '_', $resourceType));
    }

    private function deleteDirectory($directoryInstance)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $children = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')->children($directoryInstance, true);

        foreach ($children as $child) {
            $rsrc = $child->getResource();

            if ($rsrc->getInstanceCount() === 1) {

                if ($child->getResourceType()->getType() === 'directory') {
                    $em->remove($rsrc);
                } else {
                    $event = new DeleteResourceEvent(array($child->getResource()));
                    $this->get('event_dispatcher')->dispatch("delete_{$child->getResourceType()->getType()}", $event);
                }
            }

            $rsrc->removeResourceInstance($child);
            $em->remove($child);
        }

        $em->remove($directoryInstance->getResource());
        $em->flush();
    }

    private function addToDirectoryByReference(ResourceInstance $instance, ResourceInstance $parent)
    {
        if ($instance->getResource()->getShareType() == AbstractResource::PUBLIC_RESOURCE) {
            $instanceCopy = $this->createReference($instance);
            $instanceCopy->setParent($parent);
            $children = $instance->getChildren();

            foreach ($children as $child) {
                $this->addToDirectoryByReference($child, $instanceCopy);
            }

            $this->getDoctrine()->getEntityManager()->persist($instanceCopy);
        }
    }

    private function addToDirectoryByCopy(ResourceInstance $instance, ResourceInstance $parent)
    {
        if ($instance->getResource()->getShareType() == AbstractResource::PUBLIC_RESOURCE) {
            $instanceCopy = $this->createCopy($instance);
            $instanceCopy->setParent($parent);
            $children = $instance->getChildren();

            foreach ($children as $child) {
                $this->addToDirectoryByCopy($child, $instanceCopy);
            }

            $this->getDoctrine()->getEntityManager()->persist($instanceCopy);
        }
    }

    private function createCopy(ResourceInstance $resourceInstance)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $ric = new ResourceInstance();
        $ric->setCreator($user);
        $ric->setCopy(false);
        $ric->setWorkspace($resourceInstance->getWorkspace());
        $this->get('doctrine.orm.entity_manager')->flush();
        $em = $this->get('doctrine.orm.entity_manager');

        if ($resourceInstance->getResourceType()->getType()=='directory') {
            $resourceCopy = new Directory();
            $resourceCopy->setName($resourceInstance->getResource()->getName());
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByType('directory'));
            $resourceCopy->addResourceInstance($ric);
        }
        else {
            $event = new CopyResourceEvent($resourceInstance->getResource());
            $eventName = $this->normalizeEventName('copy', $resourceInstance->getResourceType()->getType());
            $this->get('event_dispatcher')->dispatch($eventName, $event);
            $resourceCopy = $event->getCopy();
            $resourceCopy->setCreator($user);
            $resourceCopy->setResourceType($resourceInstance->getResourceType());
            $resourceCopy->addResourceInstance($ric);
        }
        $em->persist($resourceCopy);
        $ric->setResource($resourceCopy);
        $em->flush();

        return $ric;
    }

    private function createReference(ResourceInstance $resourceInstance)
    {
        $ric = new ResourceInstance();
        $ric->setCreator($this->get('security.context')->getToken()->getUser());
        $ric->setCopy(true);
        $ric->setWorkspace($resourceInstance->getWorkspace());
        $ric->setResource($resourceInstance->getResource());
        $resourceInstance->getResource()->addResourceInstance($ric);

        return $ric;
    }
}