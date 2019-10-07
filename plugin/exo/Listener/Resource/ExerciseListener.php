<?php

namespace UJM\ExoBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\DocimologyManager;
use UJM\ExoBundle\Manager\ExerciseManager;

/**
 * Listens to resource events dispatched by the core.
 */
class ExerciseListener
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ExerciseManager */
    private $exerciseManager;

    /** @var PaperManager */
    private $paperManager;

    /** @var DocimologyManager */
    private $docimologyManager;

    /** @var ObjectManager */
    private $om;

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
     * @param AuthorizationCheckerInterface $authorization
     * @param ExerciseManager               $exerciseManager
     * @param PaperManager                  $paperManager
     * @param DocimologyManager             $docimologyManager
     * @param ObjectManager                 $om
     * @param ResourceEvaluationManager     $resourceEvalManager
     * @param TwigEngine                    $templating
     * @param TokenStorageInterface         $tokenStorage
     * @param SerializerProvider            $serializer
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ExerciseManager $exerciseManager,
        PaperManager $paperManager,
        DocimologyManager $docimologyManager,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        TwigEngine $templating,
        TokenStorageInterface $tokenStorage,
        SerializerProvider $serializer
    ) {
        $this->authorization = $authorization;
        $this->exerciseManager = $exerciseManager;
        $this->paperManager = $paperManager;
        $this->docimologyManager = $docimologyManager;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->templating = $templating;
        $this->tokenStorage = $tokenStorage;
        $this->serializer = $serializer;
    }

    /**
     * Loads the Exercise resource.
     *
     * @param LoadResourceEvent $event
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Exercise $exercise */
        $exercise = $event->getResource();
        $currentUser = $this->tokenStorage->getToken()->getUser();

        $canEdit = $this->authorization->isGranted('EDIT', new ResourceCollection([$exercise->getResourceNode()]));

        $options = [];
        if ($canEdit || $exercise->hasStatistics()) {
            $options[] = Transfer::INCLUDE_SOLUTIONS;
        }

        // fetch additional user data
        $nbUserPapers = 0;
        $nbUserPapersDayCount = 0;
        $userEvaluation = null;
        if ($currentUser instanceof User) {
            $nbUserPapers = (int) $this->paperManager->countUserFinishedPapers($exercise, $currentUser);
            $nbUserPapersDayCount = (int) $this->paperManager->countUserFinishedDayPapers($exercise, $currentUser);
            $userEvaluation = $this->serializer->serialize(
                $this->resourceEvalManager->getResourceUserEvaluation($exercise->getResourceNode(), $currentUser)
            );
        }

        $event->setData([
            'quiz' => $this->serializer->serialize($exercise, $options),
            'paperCount' => (int) $this->paperManager->countExercisePapers($exercise),

            // user data
            'userPaperCount' => $nbUserPapers,
            'userPaperDayCount' => $nbUserPapersDayCount,
            'userEvaluation' => $userEvaluation,
        ]);
        $event->stopPropagation();
    }

    /**
     * Deletes an Exercise resource.
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
}
