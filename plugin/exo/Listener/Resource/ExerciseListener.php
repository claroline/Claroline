<?php

namespace UJM\ExoBundle\Listener\Resource;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PublicationChangeEvent;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Form\Type\ExerciseType;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Listens to resource events dispatched by the core.
 *
 * @DI\Service("ujm_exo.listener.exercise")
 */
class ExerciseListener
{
    private $container;

    /**
     * ExerciseListener constructor.
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
     * Displays a form to create an Exercise resource.
     *
     * @DI\Observe("create_form_ujm_exercise")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        /** @var FormInterface $form */
        $form = $this->container->get('form.factory')->create(new ExerciseType());

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig', [
                'resourceType' => 'ujm_exercise',
                'form' => $form->createView(),
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    /**
     * Creates a new Exercise resource.
     *
     * @DI\Observe("create_ujm_exercise")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        /** @var FormInterface $form */
        $form = $this->container->get('form.factory')->create(new ExerciseType());
        $request = $this->container->get('request');

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine.orm.entity_manager');

            $exercise = $form->getData();
            $published = (bool) $form->get('published')->getData();
            $exercise->setPublishedOnce($published);
            $event->setPublished($published);

            $em->persist($exercise);

            $event->setResources([$exercise]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig', [
                    'resourceType' => 'ujm_exercise',
                    'form' => $form->createView(),
                ]
            );

            $event->setErrorFormContent($content);
        }

        $event->stopPropagation();
    }

    /**
     * Opens the Exercise resource.
     *
     * @DI\Observe("open_ujm_exercise")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        /** @var Request $currentRequest */
        $currentRequest = $this->container->get('request_stack')->getCurrentRequest();

        // Forward request to the Resource controller
        $subRequest = $currentRequest->duplicate($currentRequest->query->all(), null, [
            '_controller' => 'UJMExoBundle:Resource\Exercise:open',
            'id' => $exercise->getUuid(),
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Deletes an Exercise resource.
     *
     * @DI\Observe("delete_ujm_exercise")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $deletable = $this->container->get('ujm_exo.manager.exercise')->isDeletable($event->getResource());
        if (!$deletable) {
            // If papers, the Exercise is not completely removed
            $event->enableSoftDelete();
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("docimology_ujm_exercise")
     *
     * @param CustomActionResourceEvent $event
     */
    public function onDocimology(CustomActionResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        /** @var Request $currentRequest */
        $currentRequest = $this->container->get('request_stack')->getCurrentRequest();

        // Forward request to the Resource controller
        $subRequest = $currentRequest->duplicate($currentRequest->query->all(), null, [
            '_controller' => 'UJMExoBundle:Resource\Exercise:docimology',
            'id' => $exercise->getUuid(),
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Copies an Exercise resource.
     *
     * @DI\Observe("copy_ujm_exercise")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $newExercise = $this->container->get('ujm_exo.manager.exercise')->copy($event->getResource());

        $event->setCopy($newExercise);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("publication_change_ujm_exercise")
     *
     * @param PublicationChangeEvent $event
     */
    public function onPublicationChange(PublicationChangeEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        if ($exercise->getResourceNode()->isPublished()) {
            $this->container->get('ujm_exo.manager.exercise')->publish($exercise, false);
        } else {
            $this->container->get('ujm_exo.manager.exercise')->unpublish($exercise, false);
        }

        $event->stopPropagation();
    }

    /**
     * Exports an Exercise resource in SCORM format.
     *
     * @DI\Observe("export_scorm_ujm_exercise")
     *
     * @param ExportScormResourceEvent $event
     */
    public function onExportScorm(ExportScormResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        $exerciseExport = $this->container->get('ujm_exo.manager.exercise')->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS]);

        if (!empty($exerciseExport->description)) {
            $exerciseExport->description = $this->exportHtmlContent($event, $exerciseExport->description);
        }

        if ($exerciseExport->steps) {
            foreach ($exerciseExport->steps as $step) {
                $this->exportStep($event, $step);
            }
        }

        $template = $this->container->get('templating')->render(
            'UJMExoBundle:Scorm:export.html.twig', [
                '_resource' => $exercise,
                // Angular JS data
                'exercise' => $exerciseExport,
            ]
        );

        // Set export template
        $event->setTemplate($template);

        // Add template required files
        $webpack = $this->container->get('claroline.extension.webpack');
        $event->addAsset('ujm-exo.css', 'vendor/ujmexo/ujm-exo.css');
        $event->addAsset('jsPlumb-2.1.3-min.js', 'packages/jsPlumb/dist/js/jsPlumb-2.1.3-min.js');
        $event->addAsset('claroline-distribution-plugin-exo-app.js', $webpack->hotAsset('dist/claroline-distribution-plugin-exo-app.js', true));

        // Set translations
        $event->addTranslationDomain('ujm_exo');
        $event->addTranslationDomain('question_types');

        $event->stopPropagation();
    }

    private function exportStep(ExportScormResourceEvent $event, array &$step)
    {
        if ($step['meta'] && $step['meta']['description']) {
            $step['meta']['description'] = $this->exportHtmlContent($event, $step['meta']['description']);
        }

        if ($step['items']) {
            foreach ($step['items'] as $item) {
                $item->content = $this->exportHtmlContent($event, $item->content);
                $item->description = $this->exportHtmlContent($event, $item->description);

                // Export graphic question image
                if ('application/x.graphic+json' === $item->type) {
                    $filename = 'file_'.$item->id;
                    $event->addFile(
                        $filename,
                        $this->container->getParameter('claroline.param.web_dir').DIRECTORY_SEPARATOR.$item->image->url,
                        true
                    );
                    $item->image->url = '../files/'.$filename;
                }
            }
        }
    }

    private function exportHtmlContent(ExportScormResourceEvent $event, $content)
    {
        if ($content) {
            $parsed = $this->container->get('claroline.scorm.rich_text_exporter')->parse($content);
            $content = $parsed['text'];
            foreach ($parsed['resources'] as $resource) {
                $event->addEmbedResource($resource);
            }
        }

        return $content;
    }
}
