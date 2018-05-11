<?php

namespace Icap\LessonBundle\Listener;

use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Form\LessonType;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LessonListener
{
    use ContainerAwareTrait;

    /*Méthode permettant de créer le formulaire de creation*/
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            [
                'form' => $form->createView(),
                'resourceType' => 'icap_lesson',
            ]
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request_stack')->getMasterRequest();
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $lesson = $form->getData();
            $event->setResources([$lesson]);
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:create_form.html.twig',
                [
                    'form' => $form->createView(),
                    'resourceType' => 'icap_lesson',
                ]
            );
            $event->setErrorFormContent($content);
        }
        $event->stopPropagation();
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $route = $this->container
            ->get('router')
            ->generate(
                'icap_lesson',
                ['resourceId' => $event->getResource()->getId()]
                );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $lesson = $event->getResource();

        $newLesson = new Lesson();
        $newLesson->setName($lesson->getResourceNode()->getName());
        $entityManager->persist($newLesson);
        $entityManager->flush($newLesson);

        //$chapterRepository = $entityManager->getRepository('IcapLessonBundle:Chapter');
        $chapter_manager = $this->container->get('icap.lesson.manager.chapter');
        $chapter_manager->copyRoot($lesson->getRoot(), $newLesson->getRoot());

        $event->setCopy($newLesson);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $om = $this->container->get('claroline.persistence.object_manager');
        $lesson = $event->getResource();
        $om->remove($lesson);
        $om->flush();
        $event->stopPropagation();
    }

    public function onDownload(DownloadResourceEvent $event)
    {
        /*$event->setResponseContent("allo");
        $event->stopPropagation();*/
    }
}
