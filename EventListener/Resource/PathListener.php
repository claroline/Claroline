<?php

namespace Innova\PathBundle\EventListener\Resource;

use Innova\PathBundle\Entity\Step;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;

use Innova\PathBundle\Entity\Path\Path;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;

/**
 * Path Event Listener
 * Used to integrate Path to Claroline resource manager
 */
class PathListener extends ContainerAware
{
    /**
     * Fired when a ResourceNode of type Path is opened
     * @param  \Claroline\CoreBundle\Event\OpenResourceEvent $event
     * @throws \Exception
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $path = $event->getResource();
        if ($path->isPublished()) {
            // Path is published => display the Player
            $route = 'innova_path_player_wizard';
        }
        else {
            // Path is not published (so we can't play the Path) => display the Editor
            $route = 'innova_path_editor_wizard';
        }

        $url = $this->container->get('router')->generate(
            $route,
            array (
                'id' => $path->getId(),
            )
        );

        $event->setResponse(new RedirectResponse($url));
        $event->stopPropagation();
    }

    public function onAdministrate(CustomActionResourceEvent $event)
    {
        $path = $event->getResource();

        $route = $this->container->get('router')->generate(
            'innova_path_editor_wizard',
            array (
                'id' => $path->getId(),
            )
        );

        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * Fired when the form to create a new ResourceNode is displayed
     * @param \Claroline\CoreBundle\Event\CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
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
     * Fired when a new ResourceNode of type Path is opened
     * @param \Claroline\CoreBundle\Event\CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        // Create form
        $form = $this->container->get('form.factory')->create('innova_path', new Path());
        
        // Try to process form
        $request = $this->container->get('request');
        $form->handleRequest($request);

        if ($form->isValid()) {
            $path = $form->getData();

            $published = $form->get('published')->getData();
            $event->setPublished($published);

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
     * Fired when a ResourceNode of type Path is deleted
     * @param \Claroline\CoreBundle\Event\DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {

        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is duplicated
     * @param \Claroline\CoreBundle\Event\CopyResourceEvent $event
     * @throws \Exception
     */
    public function onCopy(CopyResourceEvent $event)
    {
        // Get Path to duplicate
        $pathToCopy = $event->getResource();

        // Create new Path
        $path = new Path();

        // Set up new Path properties
        $path->setName($pathToCopy->getName());
        $path->setDescription($pathToCopy->getDescription());

        $parent = $event->getParent();
        $structure = json_decode($pathToCopy->getStructure());

        // Process steps
        $processedNodes = array ();
        foreach ($structure->steps as $step) {
            $processedNodes = $this->copyStepContent($step, $parent, $processedNodes);
        }

        // Store the new structure of the Path
        $path->setStructure(json_encode($structure));

        $event->setCopy($path);

        // Force the unpublished state (the publication will recreate the correct links, and create new Activities)
        // If we directly copy all the published Entities we can't remap some relations
        $event->setPublish(false);

        $event->stopPropagation();
    }

    private function copyStepContent(\stdClass $step, ResourceNode $newParent, array $processedNodes = array ())
    {
        // Remove reference to Step Entity
        $step->resourceId = null;

        // Remove references to Activity
        $step->activityId = null;

        // Duplicate primary resources
        if (!empty($step->primaryResource) && !empty($step->primaryResource[0])) {
            $processedNodes = $this->copyResource($step->primaryResource[0], $newParent, $processedNodes);
        }

        // Duplicate secondary resources
        if (!empty($step->resources)) {
            foreach ($step->resources as $resource) {
                $processedNodes = $this->copyResource($resource, $newParent, $processedNodes);
            }
        }

        // Process step children
        if (!empty($step->children)) {
            foreach ($step->children as $child) {
                $processedNodes = $this->copyStepContent($child, $newParent, $processedNodes);
            }
        }

        return $processedNodes;
    }

    private function copyResource(\stdClass $resource, ResourceNode $newParent, array $processedNodes = array ())
    {
        // Get current User
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

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
