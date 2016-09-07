<?php

namespace UJM\ExoBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\PublicationChangeEvent;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Subscription;
use UJM\ExoBundle\Form\ExerciseType;

/**
 * @DI\Service("ujm.exo.exercise_listener")
 */
class ExerciseListener
{
    private $container;

    /**
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
     * @DI\Observe("create_form_ujm_exercise")
     *
     * @param CreateFormResourceEvent $event
     */
    public function onCreateForm(CreateFormResourceEvent $event)
    {
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
     * @DI\Observe("create_ujm_exercise")
     *
     * @param CreateResourceEvent $event
     */
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new ExerciseType());

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $user = $this->container->get('security.token_storage')->getToken()->getUser();

            $exercise = $form->getData();
            $event->setPublished((bool) $form->get('published')->getData());

            $this->container->get('ujm.exo.subscription_manager')->subscribe($exercise, $user);

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
     * @DI\Observe("open_ujm_exercise")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $subRequest = $this->container->get('request_stack')->getCurrentRequest()->duplicate([], null, [
            '_controller' => 'UJMExoBundle:Exercise:open',
            'id' => $event->getResource()->getId(),
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("delete_ujm_exercise")
     *
     * @param DeleteResourceEvent $event
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $exercise = $event->getResource();

        $nbPapers = $em->getRepository('UJMExoBundle:Paper')->countExercisePapers($event->getResource());
        if (0 === $nbPapers) {
            $this->container->get('ujm.exo.subscription_manager')->deleteSubscriptions($exercise);

            $em->remove($exercise);
        } else {
            // If papers, the Exercise is not completely removed
            $event->enableSoftDelete();

            $em->remove($exercise->getResourceNode());

            $exercise->archiveExercise();

            $em->persist($exercise);
            $em->flush();
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("copy_ujm_exercise")
     *
     * @param CopyResourceEvent $event
     */
    public function onCopy(CopyResourceEvent $event)
    {
        $newExercise = $this->container->get('ujm.exo.exercise_manager')->copyExercise($event->getResource());

        $this->container->get('doctrine.orm.entity_manager')->persist($newExercise);

        // Create Subscription for User who has copied the Exercise
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $this->container->get('ujm.exo.subscription_manager')->subscribe($newExercise, $user);

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
            $this->container->get('ujm.exo.exercise_manager')->publish($exercise, false);
        } else {
            $this->container->get('ujm.exo.exercise_manager')->unpublish($exercise, false);
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("export_scorm_ujm_exercise")
     *
     * @param ExportScormResourceEvent $event
     */
    public function onExportScorm(ExportScormResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        $exerciseExport = $this->container->get('ujm.exo.exercise_manager')->exportExercise($exercise, true);

        if ($exerciseExport['meta'] && $exerciseExport['meta']['description']) {
            $exerciseExport['meta']['description'] = $this->exportHtmlContent($event, $exerciseExport['meta']['description']);
        }

        if ($exerciseExport['steps']) {
            foreach ($exerciseExport['steps'] as $step) {
                $this->exportStep($event, $step);
            }
        }

        $template = $this->container->get('templating')->render(
            'UJMExoBundle:Scorm:export.html.twig', [
                '_resource' => $exercise,
                // Angular JS data
                'exercise' => $exerciseExport,
                'locale' => $event->getLocale(),
            ]
        );

        // Set export template
        $event->setTemplate($template);

        // Add template required files
        $webpack = $this->container->get('claroline.extension.webpack');
        $event->addAsset('ujm-exo.css', 'vendor/ujmexo/ujm-exo.css');
        $event->addAsset('jsPlumb-2.1.3-min.js', 'packages/jsPlumb/dist/js/jsPlumb-2.1.3-min.js');
        $event->addAsset('commons.js', $webpack->hotAsset('dist/commons.js', true));
        $event->addAsset('claroline-distribution-plugin-exo-app.js', $webpack->hotAsset('dist/claroline-distribution-plugin-exo-app.js', true));

        // Set translations
        $event->addTranslationDomain('ujm_exo');
        $event->addTranslationDomain('ujm_sequence');

        $event->stopPropagation();
    }

    private function exportStep(ExportScormResourceEvent $event, array &$step)
    {
        if ($step['meta'] && $step['meta']['description']) {
            $step['meta']['description'] = $this->exportHtmlContent($event, $step['meta']['description']);
        }

        if ($step['items']) {
            foreach ($step['items'] as $item) {
                $item->title = $this->exportHtmlContent($event, $item->title);
                $item->description = $this->exportHtmlContent($event, $item->description);
                $item->invite = $this->exportHtmlContent($event, $item->invite);
                $item->supplementary = $this->exportHtmlContent($event, $item->supplementary);
                $item->specification = $this->exportHtmlContent($event, $item->specification);
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

    /**
     * @DI\Observe("open_tool_desktop_ujm_questions")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate([], null, [
            '_controller' => 'UJMExoBundle:Question:index',
        ]);

        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }
}
