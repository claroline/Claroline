<?php

namespace Innova\PathBundle\Form\Handler;

use Innova\PathBundle\Manager\PathTemplateManager;

/**
 * Handles path template form
 */
class PathTemplateHandler extends AbstractHandler
{
    /**
     * Path manager
     * @var \Innova\PathBundle\Manager\PathTemplateManager
     */
    protected $pathTemplateManager;
    
    /**
     * Class constructor
     * @param \Innova\PathBundle\Manager\PathTemplateManager $pathTemplateManager
     */
    public function __construct(PathTemplateManager $pathTemplateManager)
    {
        $this->pathTemplateManager = $pathTemplateManager;
    }
    
    public function create()
    {
        $this->pathTemplateManager->create($this->data);
        
        return true;
    }
    
    public function edit()
    {
        $this->pathTemplateManager->edit($this->data);
        
        return true;
    }
}
