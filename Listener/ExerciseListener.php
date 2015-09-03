<?php

namespace UJM\ExoBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CustomActionResourceEvent;

use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\ExerciseQuestion;
use UJM\ExoBundle\Entity\Subscription;
use UJM\ExoBundle\Form\ExerciseType;

class ExerciseListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $exercise = new Exercise();
        $exercise->setNbQuestion(0);
        $exercise->setDuration(0);
        $exercise->setMaxAttempts(0);
        $exercise->setStartDate(new \Datetime());
        $exercise->setEndDate(new \Datetime());
        $exercise->setDateCorrection(new \Datetime());
        $form = $this->container->get('form.factory')
            ->create(new ExerciseType(), $exercise);
        $twig = $this->container->get('templating');
        $content = $twig->render(
            'UJMExoBundle:Exercise:new.html.twig',
            array(
                'form'  => $form->createView(),
                'resourceType' => 'ujm_exercise'
            )
        );
        $event->setResponseContent($content);
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container
            ->get('form.factory')
            ->create(new ExerciseType, new Exercise());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $user = $this->container->get('security.token_storage')->getToken()->getUser();

            $exercise = $form->getData();
            $exercise->setName($exercise->getTitle());
            $exercise->setDateCreate(new \Datetime());
            $exercise->setNbQuestionPage(1);
            $exercise->setPublished(FALSE);

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
            'UJMExoBundle:Exercise:new.html.twig',
            array(
                'resourceType' => 'ujm_exercise',
                'form'   => $form->createView()
            )
        );

        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    /**
     * Event launch when clicking the resource icon
     * @param OpenResourceEvent $event
     */
    public function onOpen(OpenResourceEvent $event)
    {
        //Redirection to the controller.
        $route = $this->container
            ->get('router')
            ->generate('ujm_exercise_play', array('id' => $event->getResource()->getId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }
    
    /**
     * Event launch when choosing Administrate exercise from the resource icon contextual menu
     * @param CustomActionResourceEvent $event
     */
    public function onAdministrate(CustomActionResourceEvent $event)
    {
        //Redirection to the controller.
        $route = $this->container
            ->get('router')
            ->generate('ujm_exercise_open', array('exerciseId' => $event->getResource()->getId()));
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $papers = $em->getRepository('UJMExoBundle:Paper')
            ->findOneBy(array(
                'exercise' => $event->getResource()->getId()
                )
            );

        if (count($papers) == 0) {

             $eqs = $em->getRepository('UJMExoBundle:ExerciseQuestion')
                ->findBy(array(
                    'exercise' => $event->getResource()->getId()
                    )
                );

            foreach ($eqs as $eq) {
                $em->remove($eq);
            }

            $subscriptions = $em->getRepository('UJMExoBundle:Subscription')
                ->findBy(array(
                    'exercise' => $event->getResource()->getId()
                    )
                );

            foreach ($subscriptions as $subscription) {
                $em->remove($subscription);
            }

            $em->flush();

            $em->remove($event->getResource());

        } else {

            $exercise = $em->getRepository('UJMExoBundle:Exercise')->find($event->getResource()->getId());
            $resourceNode = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')->find(
                $exercise->getResourceNode()->getId()
            );

            $em->remove($resourceNode);

            $exercise->archiveExercise();
            $em->persist($exercise);
            $em->flush();
            exit();
        }

        $event->stopPropagation();
    }

    public function onDisplayDesktop(DisplayToolEvent $event)
    {

        $subRequest = $this->container->get('request')->duplicate(array(), null, array("_controller" => 'UJMExoBundle:Question:index'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }

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
        $newExercise->setPublished($exerciseToCopy->getPublished());

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
}
