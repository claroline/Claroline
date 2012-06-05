<?php
namespace Claroline\CoreBundle\Library\Manager;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Symfony\Component\Form\FormFactory;

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
    
    public function getFormPage()
    {
        //$form = $this->formFactory->create(new FileType, new File());
        $content = $this->templating->render('ClarolineCoreBundle:Text:form_page.html.twig');

        return $content;
    }
    
}