<?php

namespace Claroline\HTMLPageBundle\Manager;

use Claroline\CoreBundle\Library\Manager\ResourceInterface;
use Symfony\Component\Form\FormFactory;
use Claroline\HTMLPageBundle\Form\HTMLPageType;
use Claroline\HTMLPageBundle\Entity\HTMLElement;

class HTMLPageManager// implements ResourceInterface
{
    /** @var string */
    private $dir;
    
    /** @var FormFactory */
    private $formFactory;
    
    public function __construct($dir, FormFactory $formFactory)
    {
        $this->dir = $dir;
        $this->formFactory = $formFactory;
    }
            
    public function getForm()
    {
        $form = $this->formFactory->create(new HTMLPageType, new HTMLElement());
        
        return $form;
    }
    
    public function getResourceType()
    {
        return "HTMLPage";
    }
}
