<?php

namespace UJM\ExoBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\HttpKernelInterface;


use Claroline\CoreBundle\Event\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Event\OpenResourceEvent;

use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Form\ExerciseType;
use UJM\ExoBundle\Entity\Subscription;

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
            $user = $this->container->get('security.context')->getToken()->getUser();

            $exercise = $form->getData();
            $exercise->setName($exercise->getTitle());
            $exercise->setDateCreate(new \Datetime());
            $exercise->setNbQuestionPage(1);

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

    public function onOpen(OpenResourceEvent $event)
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
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    public function onDisplayDesktop(DisplayToolEvent $event)
    {
        //$response = $this->container->get('http_kernel')->forward('UJMExoBundle:Question:index', array());
        /*$subRequest = $this->container->get('request')->*/

        $subRequest = $this->container->get('request')->duplicate(array(), null, array("_controller" => 'UJMExoBundle:Question:index'));
        $response = $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        $event->setContent($response->getContent());
    }
}