<?php
namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\HttpFoundation\Response;
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
        //redirection vers lien
    }
    
    public function getIndexAction($id)
    {
        
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
        $name = $form['name'];
        $url = $form['url'];
        $link->setName($name.'.url');
        $link->setUrl($url);
        $this->em->persist($link);
        $this->em->flush();
        
        return $link;
    }
    
    public function getForm()
    {
        $form = $this->formFactory->create(new LinkType, new Link());
        
        return $form;
    }
    
    public function getResourceType()
    {
        return "link";
    }
}