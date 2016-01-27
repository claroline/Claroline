<?php

namespace Innova\PathBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles path form
 */
abstract class AbstractHandler
{
    /**
     * Current data of the form
     * @var \Innova\PathBundle\Entity\Path\AbstractPath
     */
    protected $data;
    
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
     * Set current request
     * @param  \Symfony\Component\HttpFoundation\Request   $request
     * @return \Innova\PathBundle\Form\Handler\AbstractHandler
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
        
        return $this;
    }
    
    /**
     * Get current data of the form
     * @return \Innova\PathBundle\Entity\Path\AbstractPath
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set current form
     * @param  \Symfony\Component\Form\FormInterface $form
     * @return \Innova\PathBundle\Form\Handler\AbstractHandler
     */
    public function setForm(FormInterface $form)
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
        $success = false;
        if ($this->request->getMethod() == 'POST' || $this->request->getMethod() == 'PUT') {
            // Correct HTTP method => try to process form
            $this->form->submit($this->request);
            
            if ( $this->form->isValid() ) {
                // Form is valid => create or update the path
                $this->data = $this->form->getData();
                
                if ($this->request->getMethod() == 'POST') {
                    // Create path
                    $success = $this->create();
                }
                else {
                    // Edit existing path
                    $success = $this->edit();
                }
            }
        }
        
        return $success;
    }
    
    /**
     * Create a new path
     * @return boolean
     */
    abstract protected function create();

    /**
     * Edit existing path
     * @return boolean
     */
    abstract protected function edit();

    /**
     * @return array
     */
    public function getFormErrors()
    {
        $errors = array();
        foreach ($this->form->getErrors() as $key => $error) {
            $errors[$key] = $error->getMessage();
        }

        // Get errors from children
        foreach ($this->form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getFormErrors($child);
            }
        }
        return $errors;
    }
}
