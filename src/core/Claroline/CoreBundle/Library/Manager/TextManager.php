<?php
namespace Claroline\CoreBundle\Library\Manager;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\CoreBundle\Entity\Resource\Text;
use Claroline\CoreBundle\Form\TextType;

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
}