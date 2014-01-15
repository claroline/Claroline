<?php

namespace Innova\PathBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

use Innova\PathBundle\Manager\NonDigitalResourceManager;

/**
 * Handles non digital resource form
 */
class NonDigitalResourceHandler
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
     * Path manager
     * @var \Innova\PathBundle\Manager\NonDigitalResourceManager
     */
    protected $nonDigitalResourceManager;
    
    /**
     * Class constructor
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    public function __construct(PathManager $nonDigitalResourceManager)
    {
        $this->nonDigitalResourceManager = $nonDigitalResourceManager;
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
        if ($this->request->getMethod() == 'POST' or $this->request->getMethod() == 'PUT') {
            $this->form->handleRequest($this->request);
            
            if ( $this->form->isValid() ) {
                $message = $this->form->getData();

                return true;
            }
        }
        
        return false;
    }
}