<?php

namespace UJM\ExoBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
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
        $form->bindRequest($request);

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

            $event->setResource($exercise);
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
}
