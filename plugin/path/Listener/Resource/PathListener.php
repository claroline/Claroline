<?php

namespace Innova\PathBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Form\Type\PathType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Used to integrate Path to Claroline resource manager.
 *
 * @DI\Service("innova_path.listener.path")
 */
class PathListener
{
    private $container;

    /**
     * PathListener constructor.
     *
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Fired when a ResourceNode of type Path is opened.
     *
     * @DI\Observe("open_innova_path")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $path = $this->getPathFromEvent($event->getResource());

        // Forward request to the Resource controller
        $subRequest = $this->container->get('request_stack')->getCurrentRequest()->duplicate([], null, [
            '_controller' => 'InnovaPathBundle:Resource\Path:open',
            'id' => $path ? $path->getId() : null,
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administrate_innova_path")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onAdministrate(CustomActionResourceEvent $event)
    {
        $path = $this->getPathFromEvent($event->getResource());

        // Forward request to the Resource controller
        $subRequest = $this->container->get('request_stack')->getCurrentRequest()->duplicate([], null, [
            '_controller' => 'InnovaPathBundle:Resource\Path:edit',
            'id' => $path ? $path->getId() : null,
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Fired when the form to create a new ResourceNode is displayed.
     *
     * @DI\Observe("create_form_innova_path")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        /** @var FormInterface $form */
        $form = $this->container->get('form.factory')->create(new PathType(), new Path());

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'form' => $form->createView(),
                'resourceType' => 'innova_path',
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * Fired when a new ResourceNode of type Path is opened.
     *
     * @DI\Observe("create_innova_path")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        /** @var FormInterface $form */
        $form = $this->container->get('form.factory')->create(new PathType(), new Path());

        // Try to process the form data
        $form->handleRequest($this->container->get('request'));
        if ($form->isValid()) {
            $path = $form->getData();

            $published = $form->get('published')->getData();
            $event->setPublished($published);

            $path->setStructure('');

            // We need to force the save of the Path to get its ID
            $this->container->get('claroline.persistence.object_manager')->persist($path);
            $this->container->get('claroline.persistence.object_manager')->flush();

            // Initialize JSON structure for the Path
            $path->initializeStructure();

            // Send new path to dispatcher through event object
            $event->setResources([$path]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig', [
                    'form' => $form->createView(),
                    'resourceType' => 'innova_path',
                ]
            );

            $event->setErrorFormContent($content);
        }

        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is deleted.
     *
     * @DI\Observe("delete_innova_path")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $event->stopPropagation();
    }

    /**
     * Fired when a ResourceNode of type Path is duplicated.
     *
     * @DI\Observe("copy_innova_path")
     *
     * @param CopyResourceEvent $event
     *
     * @throws \Exception
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        // Start the transaction. We'll copy every resource in one go that way.
        $om->startFlushSuite();

        $pathToCopy = $this->getPathFromEvent($event->getResource());

        // Create new Path
        $path = new Path();

        // Set up new Path properties
        $path->setName($pathToCopy->getName());
        $path->setDescription($pathToCopy->getDescription());

        // we set the old structure to be able to insert the path in DB tp have its ID
        $path->setStructure($pathToCopy->getStructure());

        $parent = $event->getParent();
        $structure = json_decode($pathToCopy->getStructure());

        // Process steps
        $processedNodes = [];
        foreach ($structure->steps as $step) {
            $processedNodes = $this->copyStepContent($step, $parent, $processedNodes);
        }

        $om->persist($path);

        // End the transaction
        $om->endFlushSuite();
        // We need the resources ids
        $om->forceFlush();

        // update the structure tree
        foreach ($structure->steps as $step) {
            $this->updateStep($step, $processedNodes);
        }

        // Replace the Path ID by the new one
        $structure->id = $path->getId();

        $path->setStructure(json_encode($structure));
        $event->setCopy($path);

        // Force the unpublished state (the publication will recreate the correct links, and create new Activities)
        // If we directly copy all the published Entities we can't remap some relations
        $event->setPublish(false);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("unlock_innova_path")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onUnlock(CustomActionResourceEvent $event)
    {
        $path = $this->getPathFromEvent($event->getResource());

        $route = $this->container->get('router')->generate('innova_path_unlock_management', [
            'id' => $path->getId(),
        ]);

        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("manage_results_innova_path")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onManageResults(CustomActionResourceEvent $event)
    {
        $path = $this->getPathFromEvent($event->getResource());

        $route = $this->container->get('router')->generate('innova_path_manage_results', [
            'id' => $path->getId(),
        ]);

        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("export_scorm_innova_path")
     *
     * @param \Claroline\ScormBundle\Event\ExportScormResourceEvent $event
     */
    public function onExportScorm(ExportScormResourceEvent $event)
    {
        /** @var Path $path */
        $path = $event->getResource();

        // Add embed resources
        // Decode the path structure to grab embed resources ans generate resource URL
        // We export them before rendering the template to have the correct structure in twig/angular
        $structure = json_decode($path->getStructure());

        if (!empty($structure->description)) {
            $parsed = $this->container->get('claroline.scorm.rich_text_exporter')->parse($structure->description);
            $structure->description = $parsed['text'];

            foreach ($parsed['resources'] as $resource) {
                $event->addEmbedResource($resource);
            }
        }

        if (!empty($structure->steps)) {
            foreach ($structure->steps as $step) {
                $this->exportStepResources($event, $step);
            }
        }

        $template = $this->container->get('templating')->render(
            'InnovaPathBundle:Scorm:export.html.twig', [
                '_resource' => $path,
                'structure' => json_encode($structure),
            ]
        );

        // Set export template
        $event->setTemplate($template);

        // Set translations
        $event->addTranslationDomain('path_wizards');

        // Add template required files
        $webpack = $this->container->get('claroline.extension.webpack');
        $event->addAsset('tinymce.jquery.min.js', 'bundles/stfalcontinymce/vendor/tinymce/tinymce.jquery.min.js');
        $event->addAsset('jquery.tinymce.min.js', 'bundles/stfalcontinymce/vendor/tinymce/jquery.tinymce.min.js');
        $event->addAsset('claroline-distribution-plugin-path-player.js', $webpack->hotAsset('dist/claroline-distribution-plugin-path-player.js', true));
        $event->addAsset('claroline-home.js', 'bundles/clarolinecore/js/home/home.js');
        $event->addAsset('claroline-common.js', 'bundles/clarolinecore/js/common.js');
        $event->addAsset('claroline-tinymce.js', $webpack->hotAsset('dist/claroline-distribution-main-core-tinymce.js', true));

        $event->addAsset('wizards.js', 'vendor/innovapath/wizards.js');
        $event->addAsset('wizards.css', 'vendor/innovapath/wizards.css');

        $event->stopPropagation();
    }

    private function exportStepResources(ExportScormResourceEvent $event, \stdClass $step)
    {
        if (!empty($step->description)) {
            $parsed = $this->container->get('claroline.scorm.rich_text_exporter')->parse($step->description);
            $step->description = $parsed['text'];
            foreach ($parsed['resources'] as $resource) {
                $event->addEmbedResource($resource);
            }
        }

        if (!empty($step->primaryResource)) {
            foreach ($step->primaryResource as $primary) {
                $resource = $this->getResource($primary->resourceId);
                $event->addEmbedResource($resource);
                // Generate resource URL
                $primary->url = '../scos/resource_'.$primary->resourceId.'.html';
            }
        }

        if (!empty($step->resources)) {
            foreach ($step->resources as $secondary) {
                $resource = $this->getResource($secondary->resourceId);
                $event->addEmbedResource($resource);
                // Generate resource URL
                $secondary->url = '../scos/resource_'.$secondary->resourceId.'.html';
            }
        }

        if (!empty($step->children)) {
            foreach ($step->children as $child) {
                $this->exportStepResources($event, $child);
            }
        }
    }

    private function getResource($nodeId)
    {
        $node = $this->container->get('claroline.manager.resource_manager')->getById($nodeId);
        $resource = $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($node);

        return $resource;
    }

    private function copyStepContent(\stdClass $step, ResourceNode $newParent, array $processedNodes = [])
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

    private function copyResource(\stdClass $resource, ResourceNode $newParent, array $processedNodes = [])
    {
        // Get current User
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        /** @var ResourceManager $manager */
        $manager = $this->container->get('claroline.manager.resource_manager');

        $resourceNode = $manager->getNode($resource->resourceId);
        if ($resourceNode) {
            // Check if Node is in a subdirectory
            $wsRoot = $manager->getWorkspaceRoot($resourceNode->getWorkspace());
            if ($wsRoot->getId() !== $resourceNode->getParent()->getId()) {
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

            //we add the processed node
            if (empty($processedNodes[$resourceNode->getId()])) {
                // Current Node has not been processed => create a copy
                // Duplicate Node
                $copy = $manager->copy($resourceNode, $newParent, $user);
                $copyNode = $copy->getResourceNode();
                $processedNodes[$resourceNode->getId()] = $copyNode;
            }
        }

        return $processedNodes;
    }

    private function updateStep(\stdClass $step, array $processedNodes = [])
    {
        if (!empty($step->primaryResource) && !empty($step->primaryResource[0])) {
            $primaryFound = $this->replaceResourceId($step->primaryResource[0], $processedNodes);
            if (!$primaryFound) {
                unset($step->primaryResource[0]);
            }
        }

        if (!empty($step->resources)) {
            foreach ($step->resources as $index => $resource) {
                $resourceFound = $this->replaceResourceId($resource, $processedNodes);
                if (!$resourceFound) {
                    unset($step->resources[$index]);
                }
            }
        }

        // Process step children
        if (!empty($step->children)) {
            foreach ($step->children as $child) {
                $this->updateStep($child, $processedNodes);
            }
        }
    }

    /**
     * @param \stdClass      $resource
     * @param ResourceNode[] $processedNodes
     *
     * @return bool
     */
    private function replaceResourceId(\stdClass $resource, array $processedNodes)
    {
        /** @var ResourceManager $manager */
        $manager = $this->container->get('claroline.manager.resource_manager');
        $resourceNode = $manager->getNode($resource->resourceId);

        $found = false;
        if ($resourceNode) {
            $resource->resourceId = $processedNodes[$resourceNode->getId()]->getId();
            $found = true;
        }

        return $found;
    }

    /**
     * @param AbstractResource $convoyedResource
     *
     * @return Path
     */
    private function getPathFromEvent(AbstractResource $convoyedResource)
    {
        if ($convoyedResource instanceof ResourceShortcut) {
            return $this->container->get('resource_manager')->getResourceFromShortcut($convoyedResource->getResourceNode());
        } elseif ($convoyedResource instanceof Path) {
            return $convoyedResource;
        }

        return null;
    }
}
