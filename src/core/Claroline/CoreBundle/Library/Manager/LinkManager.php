<?php
namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Resource\Link;
use Claroline\CoreBundle\Form\LinkType;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Symfony\Component\Form\FormFactory;

class LinkManager //implements ResourceInterface
{
    /** @var EntityManager */
    protected $em;
    protected $formFactory;
    
    
    public function __construct(EntityManager $em, $formFactory)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
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
    
    public function getForm()
    {
        $form = $this->formFactory->create(new LinkType(), new Link());
        
        return $form;
    }
    
    public function getResourceType()
    {
        return "link";
    }
}