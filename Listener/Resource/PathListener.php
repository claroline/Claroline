<?php

namespace Innova\PathBundle\Listener\Resource;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;

use Innova\PathBundle\Entity\Path\Path;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * Path Event Listener
 * Used to integrate Path to Claroline resource manager
 */
class PathListener extends ContainerAware
{
    /**
     * Fired when a new ResourceNode of type Path is opened
     * @param  \Claroline\CoreBundle\Event\OpenResourceEvent $event
     * @throws \Exception
     */
    public function onPathOpen(OpenResourceEvent $event)
    {
        $path = $event->getResource();
        if ($path->isPublished()) {
            $route = $this->container->get('router')->generate(
                'innova_path_player_index',
                array (
                    'workspaceId' => $path->getWorkspace()->getId(),
                    'pathId' => $path->getId(),
                    'stepId' => $path->getRootStep()->getId()
                )
            );
        }
        else {
            $route = $this->container->get('router')->generate(
                'claro_workspace_open_tool',
                array(
                    'workspaceId' => $path->getWorkspace()->getId(),
                    'toolName' => 'innova_path'
                )
            );

            $this->container->get('session')->getFlashBag()->add(
                'warning',
                $this->container->get('translator')->trans("path_open_not_published_error", array(), "innova_tools")
            );
        }
        
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * Fired when a new ResourceNode of type Path is opened
     * @param \Claroline\CoreBundle\Event\CreateResourceEvent $event
     */
    public function onPathCreate(CreateResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_path', new Path());
        
        // Try to process form
        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $path = $form->getData();

            $path->initializeStructure();
            
            // Send new path to dispatcher through event object
            $event->setResources(array ($path));
        }
        else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'innova_path'
                )
            );

            $event->setErrorFormContent($content);
        }
        
        $event->stopPropagation();
    }

    /**
     * Fired when the form to create a new ResourceNode is displayed
     * @param \Claroline\CoreBundle\Event\CreateFormResourceEvent $event
     */
    public function onPathCreateForm(CreateFormResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_path', new Path());
        
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'innova_path'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is deleted
     * @param \Claroline\CoreBundle\Event\DeleteResourceEvent $event
     */
    public function onPathDelete(DeleteResourceEvent $event)
    {

        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is duplicated
     * @param \Claroline\CoreBundle\Event\CopyResourceEvent $event
     * @throws \Exception
     */
    public function onPathCopy(CopyResourceEvent $event)
    {
        // Get Path to duplicate
        $pathToCopy = $file = $event->getResource();

        // Create new Path
        $path = new Path();

        // Set up new Path properties
        $path->setName($pathToCopy->getName());
        $path->setDescription($pathToCopy->getDescription());

        $parent = $event->getParent();
        $structure = json_decode($pathToCopy->getStructure());

        $processedNodes = array ();

        // Removes Step IDs from structure
        foreach ($structure->steps as $step) {
            // Remove reference to Step Entity
            $step->resourceId = null;

            // Remove references to Activity
            $step->activityId = null;

            // Duplicate primary resources
            if (!empty($step->primaryResource)) {
                $processedNodes = $this->copyResource($step->primaryResource, $parent, $processedNodes);
            }

            // Duplicate secondary resources
            if (!empty($step->resources)) {
                foreach ($step->resources as $resource) {
                    $processedNodes = $this->copyResource($resource, $parent, $processedNodes);
                }
            }
        }

        $path->setStructure(json_encode($structure));

        $event->setCopy($path);
        $event->stopPropagation();
    }

    private function copyResource($resource, ResourceNode $newParent, array $processedNodes = array ())
    {
        // Get current User
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get resource manager
        $manager = $this->container->get('claroline.manager.resource_manager');

        $resourceNode = $manager->getNode($resource->resourceId);
        if ($resourceNode) {
            // Check if Node is in a subdirectory
            $wsRoot = $manager->getWorkspaceRoot($resourceNode->getWorkspace());
            if ($wsRoot->getId() != $resourceNode->getParent()->getId()) {
                // ResourceNode is not stored in WS root => create subdirectories tree
                $ancestors = $manager->getAncestors($resourceNode);

                foreach ($ancestors as $ancestor) {
                    if ($wsRoot->getId() !== $ancestor['id'] && $resourceNode->getId() !== $ancestor['id']) {
                        // Current node is not the WS Root and not the Node which want to duplicate
                        $parentNode = $manager->getNode($ancestor['id']);
                        if ($parentNode) {
                            if (empty($processedNodes[$parentNode->getId()])) {
                                // Current Node has not been processed => create a copy
                                $directoryRes = $manager->createResource('Claroline\CoreBundle\Entity\Resource\Directory', $parentNode->getName());
                                $directory = $manager->create(
                                    $directoryRes,
                                    $parentNode->getResourceType(),
                                    $user,
                                    $newParent->getWorkspace(),
                                    $newParent,
                                    $parentNode->getIcon()
                                );

                                $newParent = $directory->getResourceNode();
                                $processedNodes[$parentNode->getId()] = $newParent;
                            } else {
                                // Current has already been processed => get copy
                                $newParent = $processedNodes[$parentNode->getId()];
                            }
                        }
                    }
                }
            }

            if (empty($processedNodes[$resourceNode->getId()])) {
                // Current Node has not been processed => create a copy
                // Duplicate Node
                $copy = $manager->copy($resourceNode, $newParent, $user);
                $copyNode = $copy->getResourceNode();

                // Update structure with new id
                $resource->resourceId = $copy->getResourceNode()->getId();

                $processedNodes[$resourceNode->getId()] = $copyNode;
            } else {
                // Current has already been processed => get copy
                $resource->resourceId = $processedNodes[$resourceNode->getId()]->getId();
            }
        }

        return $processedNodes;
    }
}
