<?php

namespace Innova\PathBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceShortcut;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Form\ResourceNameType;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use Innova\PathBundle\Entity\InheritedResource;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\SecondaryResource;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Manager\UserProgressionManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Used to integrate Path to Claroline resource manager.
 *
 * @DI\Service("innova_path.listener.path")
 */
class PathListener
{
    private $container;

    /* @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /** @var UserProgressionManager */
    private $userProgressionManager;

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
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->serializer = $container->get('claroline.api.serializer');
        $this->userProgressionManager = $container->get('innova_path.manager.user_progression');
    }

    /**
     * Loads the Path resource.
     *
     * @DI\Observe("load_innova_path")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Path $path */
        $path = $event->getResource();

        $canEdit = $this->container
            ->get('security.authorization_checker')
            ->isGranted('EDIT', new ResourceCollection([$path->getResourceNode()]));

        $resourceTypes = [];
        if ($canEdit) {
            $resourceTypes = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findBy(['isEnabled' => true]);
        }

        $event->setAdditionalData([
            'path' => $this->serializer->serialize($path),
            'userEvaluation' => $this->userProgressionManager->getUpdatedResourceUserEvaluation($path),
            'resourceTypes' => array_map(function (ResourceType $resourceType) {
                return $this->serializer->serialize($resourceType);
            }, $resourceTypes),
        ]);
        $event->stopPropagation();
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
        /** @var Path $path */
        $path = $event->getResource();

        $canEdit = $this->container
            ->get('security.authorization_checker')
            ->isGranted('EDIT', new ResourceCollection([$path->getResourceNode()]));

        $resourceTypes = [];
        if ($canEdit) {
            $resourceTypes = $this->om
                ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findBy(['isEnabled' => true]);
        }

        $content = $this->container->get('templating')->render(
            'InnovaPathBundle:Path:open.html.twig', [
                '_resource' => $path,
                'path' => $this->serializer->serialize($path),
                'userEvaluation' => $this->userProgressionManager->getUpdatedResourceUserEvaluation($path),
                'resourceTypes' => array_map(function (ResourceType $resourceType) {
                    return $this->serializer->serialize($resourceType);
                }, $resourceTypes),
            ]
        );

        $event->setResponse(new Response($content));
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
        $form = $this->container->get('form.factory')->create(new ResourceNameType(true), new Path());

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
        $form = $this->container->get('form.factory')->create(new ResourceNameType(true), new Path());

        // Try to process the form data
        $form->handleRequest($this->container->get('request_stack')->getMasterRequest());
        if ($form->isValid()) {
            $event->setPublished(
                $form->get('published')->getData()
            );

            $event->setResources(
                [$form->getData()]
            );
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
        // Start the transaction. We'll copy every resource in one go that way.
        $this->om->startFlushSuite();

        $parent = $event->getParent();
        $pathToCopy = $this->getPathFromEvent($event->getResource());
        $pathNodes = [];
        $nodesCopy = [];

        foreach ($pathToCopy->getSteps() as $step) {
            $this->retrieveStepNodes($step, $pathNodes);
        }
        if (count($pathNodes) > 0) {
            $resourcesCopyDir = $this->createResourcesCopyDirectory($parent, $pathToCopy->getResourceNode()->getName());
            // A forced flush is required for rights purpose on the copied resources
            $this->om->forceFlush();
            $nodesCopy = $this->copyResources($pathNodes, $resourcesCopyDir->getResourceNode());
        }

        // Create new Path
        $path = new Path();

        // Set up new Path properties
        $path->setName($pathToCopy->getName());
        $path->setDescription($pathToCopy->getDescription());
        $path->setShowOverview($pathToCopy->getShowOverview());
        $path->setShowSummary($pathToCopy->getShowSummary());
        $path->setSummaryDisplayed($pathToCopy->isSummaryDisplayed());
        $path->setNumbering($pathToCopy->getNumbering());
        $path->setStructure('');

        $stepsMapping = [];

        foreach ($pathToCopy->getRootSteps() as $step) {
            $this->copyStep($step, $path, $nodesCopy, $stepsMapping);
        }

        $this->om->persist($path);

        // End the transaction
        $this->om->endFlushSuite();
        $event->setCopy($path);

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
        $event->addTranslationDomain('path');

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

    /**
     * Store in given array all resource nodes present in step.
     *
     * @param Step  $step
     * @param array $nodes
     */
    private function retrieveStepNodes(Step $step, array &$nodes)
    {
        $primaryResource = $step->getResource();
        $secondaryResources = $step->getSecondaryResources();

        if (!empty($primaryResource)) {
            $nodes[$primaryResource->getGuid()] = $primaryResource;
        }
        foreach ($secondaryResources as $secondaryResource) {
            $node = $secondaryResource->getResource();
            $nodes[$node->getGuid()] = $node;
        }
        foreach ($step->getChildren() as $child) {
            $this->retrieveStepNodes($child, $nodes);
        }
    }

    /**
     * Create directory to store copies of resources.
     *
     * @param ResourceNode $dest
     * @param string       $pathName
     *
     * @return AbstractResource
     */
    private function createResourcesCopyDirectory(ResourceNode $dest, $pathName)
    {
        // Get current User
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        /** @var ResourceManager $manager */
        $manager = $this->container->get('claroline.manager.resource_manager');
        $translator = $this->container->get('translator');

        $resourcesDir = $manager->createResource(
            'Claroline\CoreBundle\Entity\Resource\Directory',
            $pathName.' ('.$translator->trans('resources', [], 'platform').')'
        );

        return $manager->create(
            $resourcesDir,
            $dest->getResourceType(),
            $user,
            $dest->getWorkspace(),
            $dest
        );
    }

    /**
     * Copy all given resources nodes.
     *
     * @param array        $resources
     * @param ResourceNode $newParent
     *
     * @return array $resourcesCopy
     */
    private function copyResources(array $resources, ResourceNode $newParent)
    {
        $resourcesCopy = [];

        // Get current User
        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        /** @var ResourceManager $manager */
        $manager = $this->container->get('claroline.manager.resource_manager');

        foreach ($resources as $resourceNode) {
            $copy = $manager->copy($resourceNode, $newParent, $user);
            $resourcesCopy[$resourceNode->getGuid()] = $copy->getResourceNode();
        }

        return $resourcesCopy;
    }

    /**
     * Copy a step.
     *
     * @param Step  $step         The step to copy
     * @param Path  $pathCopy     The copied path
     * @param array $nodesCopy    The list of all copied resources nodes
     * @param array $stepsMapping The mapping between uuid of old steps and uuid of new ones. Useful for InheritedResources.
     * @param Step  $parent       The step parent of the copy
     */
    private function copyStep(Step $step, Path $pathCopy, array $nodesCopy, array &$stepsMapping, Step $parent = null)
    {
        $stepCopy = new Step();
        $stepCopy->setParent($parent);
        $stepCopy->setPath($pathCopy);
        $stepCopy->setTitle($step->getTitle());
        $stepCopy->setDescription($step->getDescription());
        $stepCopy->setPoster($step->getPoster());
        $stepCopy->setNumbering($step->getNumbering());
        $stepCopy->setLvl($step->getLvl());
        $stepCopy->setOrder($step->getOrder());

        $stepsMapping[$step->getUuid()] = $stepCopy->getUuid();

        $primaryResource = $step->getResource();

        if (!empty($primaryResource) && isset($nodesCopy[$primaryResource->getGuid()])) {
            $stepCopy->setResource($nodesCopy[$primaryResource->getGuid()]);
        }
        foreach ($step->getSecondaryResources() as $secondaryResource) {
            $resource = $secondaryResource->getResource();

            if (isset($nodesCopy[$resource->getGuid()])) {
                $secondaryResourceCopy = new SecondaryResource();
                $secondaryResourceCopy->setStep($stepCopy);
                $secondaryResourceCopy->setResource($nodesCopy[$resource->getGuid()]);
                $secondaryResourceCopy->setOrder($secondaryResource->getOrder());
                $secondaryResourceCopy->setInheritanceEnabled($secondaryResource->isInheritanceEnabled());
                $this->om->persist($secondaryResourceCopy);
            }
        }
        foreach ($step->getInheritedResources() as $inheritedResource) {
            $resource = $inheritedResource->getResource();

            if (isset($nodesCopy[$resource->getGuid()])) {
                $inheritedResourceCopy = new InheritedResource();
                $inheritedResourceCopy->setStep($stepCopy);
                $inheritedResourceCopy->setResource($nodesCopy[$resource->getGuid()]);
                $inheritedResourceCopy->setOrder($inheritedResource->getOrder());
                $inheritedResourceCopy->setLvl($inheritedResource->getLvl());

                if (isset($stepsMapping[$inheritedResource->getSourceUuid()])) {
                    $inheritedResource->setSourceUuid($stepsMapping[$inheritedResource->getSourceUuid()]);
                }
                $this->om->persist($inheritedResourceCopy);
            }
        }
        foreach ($step->getChildren() as $child) {
            $this->copyStep($child, $pathCopy, $nodesCopy, $stepsMapping, $stepCopy);
        }
        $this->om->persist($stepCopy);
    }
}
