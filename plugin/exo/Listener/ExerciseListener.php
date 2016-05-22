<?php

namespace UJM\ExoBundle\Listener;

use Claroline\CoreBundle\Event\PublicationChangeEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
