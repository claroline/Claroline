<?php

namespace Icap\LessonBundle\listener;

use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\LogCreateDelegateViewEvent;

use Icap\LessonBundle\Entity\Chapter;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use Icap\LessonBundle\Form\LessonType;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Controller\LessonController;

class LessonListener extends ContainerAware
{
    /*Méthode permettant de créer le formulaire de creation*/
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_lesson'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new LessonType(), new Lesson());
        $form->handleRequest($request);
        if ($form->isValid()) {
            $lesson = $form->getData();
            $event->setResources(array($lesson));
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:create_form.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'icap_lesson'
                )
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
                array('resourceId' => $event->getResource()->getId())
                );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event){
        $entityManager = $this->container->get('doctrine.orm.entity_manager');
        $lesson = $event->getResource();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $newlesson = new Lesson();
        $newlesson->setName($lesson->getResourceNode()->getName());
        $entityManager->persist($newlesson);
        $entityManager->flush($newlesson);

        $chapterRepository = $entityManager->getRepository('IcapLessonBundle:Chapter');
        $this->copyChapters($lesson->getRoot(), $newlesson->getRoot(), $newlesson, $entityManager);

        $event->setCopy($newlesson);
        $event->stopPropagation();
    }

    private function copyChapters($root_original, $root_copy, $newlesson, $entityManager){
        $chapterRepository = $entityManager->getRepository('IcapLessonBundle:Chapter');
        $chapters = $chapterRepository->children($root_original, true);
        if($chapters != null and count($chapters) > 0){
            foreach ($chapters as $chapter) {
                $newchapter = new Chapter();
                $newchapter->setTitle($chapter->getTitle());
                $newchapter->setText($chapter->getText());
                $newchapter->setLesson($newlesson);
                $chapterRepository->persistAsLastChildOf($newchapter, $root_copy);
                $entityManager->flush();
                $this->copyChapters($chapter, $newchapter, $newlesson, $entityManager);
                //$entityManager->flush();
            }
        }
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $em->flush();
        $event->stopPropagation();
    }

    public function onDownload(DownloadResourceEvent $event){
        /*$event->setResponseContent("allo");
        $event->stopPropagation();*/
    }


}