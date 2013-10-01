<?php

namespace Icap\WikiBundle\Listener;

use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Event\CreateFormResourceEvent;
use Claroline\CoreBundle\Event\CreateResourceEvent;
use Claroline\CoreBundle\Event\DeleteResourceEvent;
use Claroline\CoreBundle\Event\OpenResourceEvent;
use Claroline\CoreBundle\Event\CopyResourceEvent;
use Claroline\CoreBundle\Event\LogCreateDelegateViewEvent;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use Icap\WikiBundle\Entity\Wiki;
use Icap\WikiBundle\Entity\Section;
use Icap\WikiBundle\Form\WikiType;

class WikiListener extends ContainerAware
{
    public function onCreateForm(CreateFormResourceEvent $event)
    {
        $form = $this->container->get('form.factory')->create(new WikiType(), new Wiki());
        $content = $this->container->get('templating')->render(
            'ClarolineCoreBundle:Resource:createForm.html.twig',
            array(
                'form' => $form->createView(),
                'resourceType' => 'icap_wiki'
            )
        );

        $event->setResponseContent($content);
        $event->stopPropagation();
    }
    
    public function onCreate(CreateResourceEvent $event)
    {
        $request = $this->container->get('request');
        $form = $this->container->get('form.factory')->create(new WikiType(), new Wiki());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $wiki = $form->getData();
            $event->setResources(array($wiki));
        } else {
            $content = $this->container->get('templating')->render(
                'ClarolineCoreBundle:Resource:createForm.html.twig',
                array(
                    'form' => $form->createView(),
                    'resourceType' => 'icap_wiki'
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
                'icap_wiki_view',
                array('wikiId' => $event->getResource()->getId())
            );
        $event->setResponse(new RedirectResponse($route));
        $event->stopPropagation();
    }

    public function onDelete(DeleteResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($event->getResource());
        $em->flush();
        $event->stopPropagation(); 
    }

    public function onCopy(CopyResourceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        
        $wiki = $event->getResource();
        $oldRoot = $wiki->getRoot();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $sectionRepository = $em->getRepository('IcapWikiBundle:Section');
        $sections = $sectionRepository->children($oldRoot);
        $newSectionsMap = array();

        $newWiki = new Wiki();
        $newWiki->setName($wiki->getName());
        $em->persist($newWiki);
        $em->flush($newWiki);
        
        $newRoot = $newWiki->getRoot();
        $newRoot->setText($oldRoot->getText());
        $newSectionsMap[$oldRoot->getId()] = $newRoot;

        foreach ($sections as $section) {
            $newSection = new Section();
            $newSection->setWiki($newWiki);
            $newSection->setTitle($section->getTitle());
            $newSection->setText($section->getText());
            $newSection->setVisible($section->getVisible());
            $newSectionParent = $newSectionsMap[$section->getParent()->getId()];
            $newSection->setParent($newSectionParent);

            $newSectionsMap[$section->getId()] = $newSection;
            $sectionRepository->persistAsLastChildOf($newSection, $newSectionParent);
        }

        $event->setCopy($newWiki);
        $event->stopPropagation(); 
    }
}