<?php

namespace UJM\ExoBundle\Listener\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Event\Resource\OpenResourceEvent;
use Claroline\CoreBundle\Event\Resource\PublicationChangeEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Twig\WebpackExtension;
use Claroline\ScormBundle\Event\ExportScormResourceEvent;
use Claroline\ScormBundle\Library\Export\RichTextExporter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Form\Type\ExerciseType;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Listens to resource events dispatched by the core.
 *
 * @DI\Service("ujm_exo.listener.exercise")
 */
class ExerciseListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ExerciseManager */
    private $exerciseManager;

    /** @var FormFactory */
    private $formFactory;

    /** @var HttpKernelInterface */
    private $httpKernel;

    /** @var ObjectManager */
    private $om;

    /** @var RequestStack */
    private $request;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var RichTextExporter */
    private $richTextExporter;

    /** @var TwigEngine */
    private $templating;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var string */
    private $webDir;

    /** @var WebpackExtension */
    private $webpack;

    /**
     * ExerciseListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager"     = @DI\Inject("ujm_exo.manager.exercise"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "httpKernel"          = @DI\Inject("http_kernel"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"             = @DI\Inject("request_stack"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "richTextExporter"    = @DI\Inject("claroline.scorm.rich_text_exporter"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "webDir"              = @DI\Inject("%claroline.param.web_dir%"),
     *     "webpack"             = @DI\Inject("claroline.extension.webpack")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param FormFactory                   $formFactory
     * @param HttpKernelInterface           $httpKernel
     * @param ObjectManager                 $om
     * @param RequestStack                  $request
     * @param ResourceEvaluationManager     $resourceEvalManager
     * @param RichTextExporter              $richTextExporter
     * @param TwigEngine                    $templating
     * @param TokenStorageInterface         $tokenStorage
     * @param string                        $webDir
     * @param WebpackExtension              $webpack
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        ObjectManager $om,
        RequestStack $request,
        ResourceEvaluationManager $resourceEvalManager,
        RichTextExporter $richTextExporter,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        $webDir,
        WebpackExtension $webpack
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->om = $om;
        $this->request = $request;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->richTextExporter = $richTextExporter;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->webDir = $webDir;
        $this->webpack = $webpack;
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
        $form = $this->formFactory->create(new ExerciseType());

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:resource:create_form.html.twig', [
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
        $form = $this->formFactory->create(new ExerciseType());
        $request = $this->request->getMasterRequest();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $exercise = $form->getData();
            $published = (bool) $form->get('published')->getData();
            $exercise->setPublishedOnce($published);
            $event->setPublished($published);

            $this->om->persist($exercise);

            $event->setResources([$exercise]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:resource:create_form.html.twig', [
                    'resourceType' => 'ujm_exercise',
                    'form' => $form->createView(),
                ]
            );

            $event->setErrorFormContent($content);
        }

        $event->stopPropagation();
    }

    /**
     * Loads the Exercise resource.
     *
     * @DI\Observe("load_ujm_exercise")
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        $canEdit = $this->authorization->isGranted('EDIT', new ResourceCollection([$exercise->getResourceNode()]));

        $event->setAdditionalData([
            'quiz' => $this->exerciseManager->serialize(
                $exercise,
                $canEdit ? [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_METRICS] : [Transfer::INCLUDE_METRICS]
            ),
        ]);
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
        $user = $this->tokenStorage->getToken()->getUser();

        $canEdit = $this->authorization->isGranted('EDIT', new ResourceCollection([$exercise->getResourceNode()]));

        $content = $this->templating->render(
            'UJMExoBundle:exercise:open.html.twig', [
                '_resource' => $exercise,
                'quiz' => $this->exerciseManager->serialize(
                    $exercise,
                    $canEdit ? [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_METRICS] : [Transfer::INCLUDE_METRICS]
                ),
                'userEvaluation' => 'anon.' === $user ?
                    null :
                    $this->resourceEvalManager->getResourceUserEvaluation($exercise->getResourceNode(), $user),
            ]
        );

        $event->setResponse(new Response($content));
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
        $deletable = $this->exerciseManager->isDeletable($event->getResource());
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
        $currentRequest = $this->request->getCurrentRequest();

        // Forward request to the Resource controller
        $subRequest = $currentRequest->duplicate($currentRequest->query->all(), null, [
            '_controller' => 'UJMExoBundle:Resource\Exercise:docimology',
            'id' => $exercise->getUuid(),
        ]);

        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);

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
        $newExercise = $this->exerciseManager->copy($event->getResource());

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
            $this->exerciseManager->publish($exercise, false);
        } else {
            $this->exerciseManager->unpublish($exercise, false);
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

        $exerciseExport = $this->exerciseManager->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS]);

        if (!empty($exerciseExport->description)) {
            $exerciseExport->description = $this->exportHtmlContent($event, $exerciseExport->description);
        }

        if ($exerciseExport->steps) {
            foreach ($exerciseExport->steps as $step) {
                $this->exportStep($event, $step);
            }
        }

        $template = $this->templating->render(
            'UJMExoBundle:Scorm:export.html.twig', [
                '_resource' => $exercise,
                // Angular JS data
                'exercise' => $exerciseExport,
            ]
        );

        // Set export template
        $event->setTemplate($template);

        // Add template required files
        $event->addAsset('ujm-exo.css', 'vendor/ujmexo/ujm-exo.css');
        $event->addAsset('jsPlumb-2.1.3-min.js', 'packages/jsplumb/dist/js/jsPlumb-2.1.3-min.js');
        $event->addAsset('claroline-distribution-plugin-exo-app.js', $this->webpack->hotAsset('dist/claroline-distribution-plugin-exo-app.js', true));

        // Set translations
        $event->addTranslationDomain('quiz');
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
                        $this->webDir.DIRECTORY_SEPARATOR.$item->image->url,
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
            $parsed = $this->richTextExporter->parse($content);
            $content = $parsed['text'];
            foreach ($parsed['resources'] as $resource) {
                $event->addEmbedResource($resource);
            }
        }

        return $content;
    }
}
