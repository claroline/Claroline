<?php

namespace Innova\PathBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

use Innova\PathBundle\Manager\PathManager;

/**
 * Handles path form
 */
class PathHandler
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
     * @var \Innova\PathBundle\Manager\PathManager
     */
    protected $pathManager;
    
    /**
     * Class constructor
     * @param \Innova\PathBundle\Manager\PathManager $pathManager
     */
    public function __construct(PathManager $pathManager)
    {
        $this->pathManager = $pathManager;
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
                $path = $this->form->getData();
                
                // Retrieve current Workspace
                $workspaceId = $this->request->get('workspaceId');
                
                $this->pathManager->create($path);
                
                return true;
            }
        }
        
        return false;
    }
}