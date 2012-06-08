<?php
namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\Link;
use Claroline\CoreBundle\Form\LinkType;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;

class LinkManager //implements ResourceInterface
{
    /** @var EntityManager */
    protected $em;
    /** @var FormFactory */
    protected $formFactory;
    /** @var TwigEngine */
    protected $templating;
    
    
    public function __construct(EntityManager $em, FormFactory $formFactory, TwigEngine $templating)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->templating = $templating;
    }
    
    public function getDefaultAction($id)
    {
        $link = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\Link')->find($id);
        return new RedirectResponse($link->getUrl());
    }
    
    public function getIndexAction($workspaceId, $id)
    {
        //openAction
        //check mime type ?
        //check resource type ?
    }
    
    public function delete($id)
    {
        
    }
    
    public function copy($id)
    {
        
    }
    
    public function add($form, $id, $user)
    {
        $link = new Link();
        $name = $form['name']->getData();
        $url = $form['url']->getData();
        $type = $form['type']->getData();
        $link->setName($name.'.url');
        $link->setUrl($url);
        $link->setResourceType($type);
        $this->em->persist($link);
        $this->em->flush();
        
        return $link;
    }
    
    public function getFormPage($twigFile, $id, $type)
    {
        $form = $this->formFactory->create(new LinkType(), new Link());
        $content = $this->templating->render(
            $twigFile, array('form' => $form->createView(), 'id' => $id, 'type' =>$type)
        );
        
        return $content;;
    }
    
    public function getResourceType()
    {
        return "link";
    }
}