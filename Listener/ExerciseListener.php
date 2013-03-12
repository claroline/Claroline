<?php

namespace UJM\ExoBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use UJM\ExoBundle\Entity\Exercise;
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
        //the form you defined with the symfony2 form component
        $form = $this->container
            ->get('form.factory')
            ->create(new ExerciseType, new Exercise());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $exercise = $form->getData();
            $exercise->setName($exercise->getTitle());
            $exercise->setDateCreate(new \Datetime());
            $exercise->setNbQuestionPage(1);
            //give it back to the event.
            $event->setResource($exercise);
            $event->stopPropagation();

            return;
        }
        
        //if the form is invalid, renders the form with its errors.
        $content = $this->container->get('templating')->render(
            'UJMExoBundle:Exercise:new.html.twig',
            array(
                'resourceType' => 'ujm_exercise',
                //'entity' => $entity,
                'form'   => $form->createView()
            )
        );
        //give it back to the event.
        $event->setErrorFormContent($content);
        $event->stopPropagation();
        
    }
}