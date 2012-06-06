<?php
namespace Claroline\CoreBundle\Library\Manager;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Entity\Resource\TextContent;
use Claroline\CoreBundle\Form\TextType;
use Symfony\Component\HttpFoundation\Response;

class TextManager //implements ResourceInterface
{
    /** @var EntityManager */
    protected $em;
    /** @var FormFactory */
    protected $formFactory;
    protected $templating;
    
    public function __construct(EntityManager $em, $formFactory, $templating)
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
        return $this->formFactory->create(new TextType, new Text());
    }
    
    public function getFormPage($twigTemp, $id, $type)
    {
        $form = $this->formFactory->create(new TextType, new Text());
        $content = $this->templating->render('ClarolineCoreBundle:Text:form_page.html.twig', array('form' => $form->createView(), 'id' => $id, 'type' => $type));
        
        return $content;
    }  
    
    public function add($form, $id, $user)
    {
        //
         $name = $form['name']->getData();
         $data = $form['text']->getData();
         $content = new TextContent();
         $content->setContent($data);
         $content->setUser($user);
         $this->em->persist($content);
         $text = new Text();
         $text->setName($name);
         $text->setText($content);
         $this->em->persist($text);
         $content->setText($text);
         $this->em->flush();
         
         return $text;
    }
    
    public function getDefaultAction($id)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $content = $this->templating->render('ClarolineCoreBundle:Text:index.html.twig', array('text' => $text->getText()->getContent()));
        
        return new Response($content);
    }
    
    public function editAction($id)
    {
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\Text')->find($id);
        $content = $this->templating->render('ClarolineCoreBundle:Text:edit.html.twig', array('text' => $text->getText()->getContent(), 'id' => $id));
        
        return new Response($content);
    }
}