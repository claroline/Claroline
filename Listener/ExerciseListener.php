<?php

namespace UJM\ExoBundle\Listener;

use Claroline\CoreBundle\Event\PublicationChangeEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
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
        $form = $this->container->get('form.factory')
            ->create(new ExerciseType(true));
        $twig = $this->container->get('templating');
        $content = $twig->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form'  => $form->createView(),
                'resourceType' => 'ujm_exercise'
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
        $form = $this->container
            ->get('form.factory')
            ->create(new ExerciseType(true));
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $user = $this->container->get('security.token_storage')->getToken()->getUser();

            $exercise = $form->getData();
            $exercise->setName($exercise->getTitle());
            $event->setPublished((bool) $form->get('publish')->getData());

            $subscription = new Subscription($user, $exercise);
            $subscription->setAdmin(true);
            $subscription->setCreator(true);

            $em->persist($exercise);
            $em->persist($subscription);

            $event->setResources(array($exercise));
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'resourceType' => 'ujm_exercise',
                'form'   => $form->createView()
            ]
        );

        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_ujm_exercise")
     *
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        $subRequest = $this->container->get('request_stack')
            ->getCurrentRequest()
            ->duplicate([], null, [
                '_controller' => 'UJMExoBundle:Sequence\Sequence:play',
                'id' => $event->getResource()->getId()
            ]);
        $response = $this->container->get('http_kernel')
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * Event launched when choosing Administrate exercise from the resource icon contextual menu
     * @DI\Observe("ujm_exercise_administrate_ujm_exercise")
     * @param CustomActionResourceEvent $event
     */
    public function onAdministrate(CustomActionResourceEvent $event)
    {
        $subRequest = $this->container->get('request_stack')
            ->getCurrentRequest()
            ->duplicate([], null, [
                '_controller' => 'UJMExoBundle:Exercise:open',
                'id' => $event->getResource()->getId()
            ]);
        $response = $this->container->get('http_kernel')
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
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

        $papers = $em->getRepository('UJMExoBundle:Paper')
            ->findOneByExercise($event->getResource());

        if (count($papers) == 0) {

             $eqs = $em->getRepository('UJMExoBundle:ExerciseQuestion')
                ->findByExercise($event->getResource());

            foreach ($eqs as $eq) {
                $em->remove($eq);
            }

            $subscriptions = $em->getRepository('UJMExoBundle:Subscription')
                ->findByExercise($event->getResource());

            foreach ($subscriptions as $subscription) {
                $em->remove($subscription);
            }

            $em->flush();

            $em->remove($event->getResource());

        } else {
            $exercise = $event->getResource();
            $resourceNode = $exercise->getResourceNode();

            $em->remove($resourceNode);
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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();

        $exerciseToCopy = $em->getRepository('UJMExoBundle:Exercise')->find($resource->getId());
        $listQuestionsExoToCopy = $em->getRepository('UJMExoBundle:ExerciseQuestion')
                                     ->findBy(array('exercise' => $exerciseToCopy->getId()));

        $newExercise = new Exercise();
        $newExercise->setName($resource->getName());
        $newExercise->setTitle($exerciseToCopy->getTitle());
        $newExercise->setDescription($exerciseToCopy->getDescription());
        $newExercise->setShuffle($exerciseToCopy->getShuffle());
        $newExercise->setNbQuestion($exerciseToCopy->getNbQuestion());
        $newExercise->setDateCreate($exerciseToCopy->getDateCreate());
        $newExercise->setDuration($exerciseToCopy->getDuration());
        $newExercise->setNbQuestionPage($exerciseToCopy->getNbQuestionPage());
        $newExercise->setDoprint($exerciseToCopy->getDoprint());
        $newExercise->setMaxAttempts($exerciseToCopy->getMaxAttempts());
        $newExercise->setCorrectionMode($exerciseToCopy->getCorrectionMode());
        $newExercise->setDateCorrection($exerciseToCopy->getDateCorrection());
        $newExercise->setMarkMode($exerciseToCopy->getMarkMode());
        $newExercise->setStartDate($exerciseToCopy->getStartDate());
        $newExercise->setUseDateEnd($exerciseToCopy->getUseDateEnd());
        $newExercise->setEndDate($exerciseToCopy->getEndDate());
        $newExercise->setDispButtonInterrupt($exerciseToCopy->getDispButtonInterrupt());
        $newExercise->setLockAttempt($exerciseToCopy->getLockAttempt());
        

        $em->persist($newExercise);
        $em->flush();

        foreach ($listQuestionsExoToCopy as $eq) {
            $questionToAdd = $em->getRepository('UJMExoBundle:Question')->find($eq->getQuestion());;
            $exerciseQuestion = new ExerciseQuestion($newExercise, $questionToAdd);
            $exerciseQuestion->setOrdre($eq->getOrdre());

            $em->persist($exerciseQuestion);
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $subscription = new Subscription($user, $newExercise);
        $subscription->setAdmin(true);
        $subscription->setCreator(true);
        $em->persist($subscription);

        $em->flush();

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
        $exercise = $event->getResource();

        if ($exercise->getResourceNode()->isPublished() && !$exercise->wasPublishedOnce()) {
            $this->container->get('ujm.exo.exercise_manager')->deletePapers($exercise);
            $exercise->setPublishedOnce(true);
            $this->container->get('claroline.persistence.object_manager')->flush();
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_ujm_questions")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        $subRequest = $this->container->get('request')->duplicate([], null, ['_controller' => 'UJMExoBundle:Question:index']);
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
    }
}
