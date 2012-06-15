<?php

namespace Claroline\ForumBundle\Manager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Form\ForumType;

/**
 * this service is called when the resource controller is using a "manager" resource
 */
class ForumManager
{
    /** @var FormFactory */
    protected $formFactory;

    /** @var TwigEngine */
    protected $templating;

    /** @var EntityManager */
    protected $em;

    public function __construct(FormFactory $formFactory, TwigEngine $templating, EntityManager $em)
    {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->em = $em;
    }

    public function getResourceType()
    {
        return 'Forum';
    }

    public function getForm()
    {
        return $this->formFactory->create(new ForumType, new Forum());
    }

    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->formFactory->create(new ForumType, new Forum());

        return $this->templating->render(
            $twigFile, array('form' => $form->createView(), 'parentId' => $id, 'type' => $type)
        );
    }

    public function add($form, $id, User $user)
    {
        $name = $form['name']->getData();
        $forum = new Forum();
        $forum->setName($name);
        $this->em->persist($forum);
        $this->em->flush();

        return $forum;
    }

    public function getDefaultAction($resourceId)
    {
        $forum = $this->em->getRepository('Claroline\ForumBundle\Entity\Forum')->find($resourceId);
        $content = $this->templating->render(
            'ClarolineForumBundle::index.html.twig', array('forum' => $forum)
        );

        $response = new Response($content);

        return $response;
    }
}