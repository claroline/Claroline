<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Claroline\CoreBundle\Form\TextType;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Library\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Library\Event\CreateResourceEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Library\Event\OpenResourceEvent;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;

class TextListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new TextType, new Text());
        $response = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'text'
            )
        );
        $event->setResponseContent($response);
        $event->stopPropagation();
    }

    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $em = $this->container->get('doctrine.orm.entity_manager');
        $user = $this->container->get('security.context')->getToken()->getUser();
        $form = $this->container
            ->get('form.factory')
            ->create(new TextType(), new Text());
        $form->bindRequest($request);

        if ($form->isValid()) {
            $revision = new Revision();
            $revision->setContent($form->getData()->getText());
            $revision->setUser($user);
            $em->persist($revision);
            $text = new Text();
            $text->setLastRevision($revision);
            $text->setName($form->getData()->getName());
            $em->persist($text);
            $revision->setText($text);
            $event->setResource($text);
            $event->stopPropagation();

            return;
        }

        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:create_form.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'text'
            )
        );
        $event->setErrorFormContent($content);
        $event->stopPropagation();
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $resource = $event->getResource();
        $revisions = $resource->getRevisions();
        $copy = new Text();

        foreach ($revisions as $revision) {
            $rev = new Revision();
            $rev->setVersion($revision->getVersion());
            $rev->setContent($revision->getContent());
            $rev->setUser($revision->getUser());
            $rev->setText($copy);
            $em->persist($rev);
        }

        $copy->setLastRevision($resource->getLastRevision());
        $event->setCopy($copy);
    }

    public function onOpen(OpenResourceEvent $event)
    {
        $text = $event->getResource();
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Text:index.html.twig',
            array(
                'text' => $text->getLastRevision()->getContent(),
                'textId' => $event->getResource()->getId(),
                'workspace' => $text->getWorkspace()
            )
        );
        $response = new Response($content);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $event->stopPropagation();
    }

    public function onExportTemplate(ExportResourceTemplateEvent $event)
    {
        $text = $event->getResource();
        $config['text'] = $text->getLastRevision()->getContent();
        $event->setConfig($config);
        $event->stopPropagation();
    }

    public function onImportTemplate(ImportResourceTemplateEvent $event)
    {
//        $em = $this->container->get('doctrine.orm.entity_manager');
//        $user = $this->container->get('security.context')->getToken()->getUser();
//        $text = new Text();
//        $config = $event->getConfig();
//        $revision = new Revision();
//        $revision->setContent($config['text']);
//        $revision->setUser($user);
//        $revision->setText($text);
//        $em->persist($revision);
//        $text->setLastRevision($revision);
//        $event->setResource($text);
//        $event->stopPropagation();
    }


}