<?php
namespace Claroline\CoreBundle\Library\Manager;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\Revision;
use Claroline\CoreBundle\Form\TextType;

class TextManager //implements ResourceInterface
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
    
    public function getResourceType()
    {
        return "text";
    }
    
    public function getForm()
    {
        return $this->formFactory->create(new TextType);
    }
    
    public function getFormPage($twigTemp, $id, $type)
    {
        $form = $this->formFactory->create(new TextType);
        $content = $this->templating->render('ClarolineCoreBundle:Text:form_page.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' => $type));
        
        return $content;
    }  
    
    public function add($form, $id, $user)
    {
         $name = $form['name']->getData();
         $data = $form['text']->getData();
         $revision = new Revision();
         $revision->setContent($data);
         $revision->setUser($user);
         $this->em->persist($revision);
         $text = new Text();
         $text->setName($name);
         $text->setLastRevision($revision);
         $this->em->persist($text);
         $revision->setText($text);
         $this->em->flush();
         
         return $text;
    }
    
    public function getDefaultAction($id)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $content = $this->templating->render('ClarolineCoreBundle:Text:index.html.twig', array('text' => $text->getLastRevision()->getContent(), 'id' => $id));
        
        return new Response($content);
    }
    
    public function editAction($id)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $content = $this->templating->render('ClarolineCoreBundle:Text:edit.html.twig', array('text' => $text->getLastRevision()->getContent(), 'id' => $id));
        
        return new Response($content);
    }  
}