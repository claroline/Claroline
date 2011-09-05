<?php

namespace Claroline\PluginBundle\Service\PluginManager;

use Claroline\PluginBundle\AbstractType\ClarolineTool;
use Claroline\PluginBundle\Service\PluginManager\Exception\ValidationException;

class ToolValidator
{
    private $tool;
    private $toolFQCN;
    
    public function check(ClarolineTool $tool)
    {
        $this->tool = $tool;
        $this->toolFQCN = get_class($tool);
    }
}