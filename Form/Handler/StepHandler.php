<?php

namespace Innova\PathBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Innova\PathBundle\Manager\StepManager;


/**
 * Handles Step form
 */
class StepHandler
{
    /**
     * Form to handle
     * @var \Symfony\Component\Form\Form
     */
    protected $form;
    
    /**
     * Current request
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * Step manager
     * @var \Innova\PathBundle\Manager\StepManager
     */
    protected $stepManager;
    
    /**
     * Class constructor
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    public function __construct(StepManager $stepManager)
    {
        $this->stepManager = $stepManager;
    }


    /**
     * Set current request
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @return \Innova\PathBundle\Form\Handler\PathHandler
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * Set current form
     * @param  \Symfony\Component\Form\Form $form
     * @return \Innova\PathBundle\Form\Handler\PathHandler
     */
    public function setForm(Form $form)
    {
        $this->form = $form;
    
        return $this;
    }
    
    /**
     * Process current form
     * @return boolean
     */
    public function process()
    {
        if ($this->request->getMethod() == 'POST' || $this->request->getMethod() == 'PUT') {
            $this->form->handleRequest($this->request);
            
            if ( $this->form->isValid() ) {
                $step = $this->form->getData();
                $this->stepManager->contextualUpdate($step);
                
                return true;
            }
        }
        
        return false;
    }
}