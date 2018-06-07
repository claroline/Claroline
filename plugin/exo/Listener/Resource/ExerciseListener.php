<?php

namespace UJM\ExoBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
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
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Form\Type\ExerciseType;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\DocimologyManager;
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

    /** @var DocimologyManager */
    private $docimologyManager;

    /** @var FormFactory */
    private $formFactory;

    /** @var ObjectManager */
    private $om;

    /** @var RequestStack */
    private $request;

    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    /** @var TwigEngine */
    private $templating;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * ExerciseListener constructor.
     *
     * @DI\InjectParams({
     *     "authorization"       = @DI\Inject("security.authorization_checker"),
     *     "exerciseManager"     = @DI\Inject("ujm_exo.manager.exercise"),
     *     "docimologyManager"   = @DI\Inject("ujm_exo.manager.docimology"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "request"             = @DI\Inject("request_stack"),
     *     "resourceEvalManager" = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "templating"          = @DI\Inject("templating"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param DocimologyManager             $docimologyManager
     * @param FormFactory                   $formFactory
     * @param ObjectManager                 $om
     * @param RequestStack                  $request
     * @param ResourceEvaluationManager     $resourceEvalManager
     * @param TwigEngine                    $templating
     * @param TokenStorageInterface         $tokenStorage
     * @param SerializerProvider            $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        DocimologyManager $docimologyManager,
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $request,
        ResourceEvaluationManager $resourceEvalManager,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->docimologyManager = $docimologyManager;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $request;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
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

        $content = $this->templating->render(
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
            $content = $this->templating->render(
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
        $user = $this->tokenStorage->getToken()->getUser();

        $canEdit = $this->authorization->isGranted('EDIT', new ResourceCollection([$exercise->getResourceNode()]));

        $event->setAdditionalData([
            'quiz' => $this->exerciseManager->serialize(
                $exercise,
                $canEdit ? [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_METRICS] : [Transfer::INCLUDE_METRICS]
            ),
            'evaluation' => 'anon.' === $user ?
                null :
                $this->serializer->serialize(
                    $this->resourceEvalManager->getResourceUserEvaluation($exercise->getResourceNode(), $user)
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
                    $this->resourceEvalManager->getResourceUserEvaluation($exercise->getResourceNode(), $user)
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
        /** @var Exercise $exercise */
        $exercise = $event->getResource();

        $deletable = $this->exerciseManager->isDeletable($exercise);
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

        $content = $this->templating->render(
            'UJMExoBundle:exercise:docimology.html.twig', [
                '_resource' => $exercise,
                'exercise' => $this->exerciseManager->serialize($exercise, [Transfer::MINIMAL]),
                'statistics' => $this->docimologyManager->getStatistics($exercise, 100),
            ]
        );

        $event->setResponse(new Response($content));
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
        /** @var Exercise $resource */
        $resource = $event->getResource();

        $newExercise = $this->exerciseManager->copy($resource);

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
            $this->exerciseManager->publish($exercise);
        } else {
            $this->exerciseManager->unpublish($exercise);
        }

        $event->stopPropagation();
    }
}
