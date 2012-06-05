<?php
namespace Claroline\CoreBundle\Library\Manager;

use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Library\Security\RightManager\RightManagerInterface;
use Symfony\Component\Form\FormFactory;

class TextManager //implements ResourceInterface
{
    /** @var EntityManager */
    protected $em;
    protected $formFactory;
    
    
    public function __construct(EntityManager $em, $formFactory)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
    }
    
    public function getResourceType()
    {
        return "text";
    }
    
    public function getForm()
    {
        //~ tinyMCE Editor here.
        return $form;
    }
    
}