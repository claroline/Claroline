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
        $entity = new Exercise();
        $entity->setNbQuestion(0);
        $entity->setDuration(0);
        $entity->setMaxAttempts(0);
        $entity->setStartDate(new \Datetime());
        $entity->setEndDate(new \Datetime());
        $entity->setDateCorrection(new \Datetime());
        $form = $this->container->get('form.factory')
            ->create(new ExerciseType(), $entity);
        $twig = $this->container->get('templating');
        $content = $twig->render(
            'UJMExoBundle:Exercise:new.html.twig',
            array(
                'entity' => $entity,
                'form'   => $form->createView()
            )
        );
        $event->setResponseContent($content);
    }
}