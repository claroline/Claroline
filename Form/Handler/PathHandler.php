<?php

namespace Innova\PathBundle\Form\Handler;

use Innova\PathBundle\Manager\PathManager;

/**
 * Handles path form
 */
class PathHandler extends AbstractHandler
{
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
    
    public function create()
    {
        // Retrieve current Workspace
        $workspaceId = $this->request->get('workspaceId');
        $workspace = $this->pathManager->getWorkspace($workspaceId);
        
        $this->pathManager->create($this->data, $workspace);
        
        return true;
    }
    
    public function edit()
    {
        $this->pathManager->edit($this->data);
        
        return true;
    }
}