<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Claroline\CoreBundle\Exception\ClarolineException;

class Configuration
{
    const TYPE_SIMPLE = 'Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace';
    const TYPE_AGGREGATOR = 'Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace';
    
    private $workspaceType;
    private $workspaceName;
    private $isPublic;
    private $visitorTranslationKey;
    private $collaboratorTranslationKey;
    private $managerTranslationKey;
    private $type;
    
    public function __construct()
    {
        $this->workspaceType = self::TYPE_SIMPLE;
        $this->isPublic = true;
        $this->visitorTranslationKey = 'Visitor';
        $this->collaboratorTranslationKey = 'Collaborator';
        $this->managerTranslationKey = 'Manager';
        $this->type = 'standard';
    }
    
    public static function fromTemplate($templateFile)
    {
        throw new \Exception('Not implemented yet');
    }
    
    public function setWorkspaceType($type)
    {
        $this->workspaceType = $type;
    }
    
    public function getWorkspaceType()
    {
        return $this->workspaceType;
    }
    
    public function setWorkspaceName($name)
    {
        $this->workspaceName = $name;
    }
    
    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }
    
    public function setPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }
    
    public function isPublic()
    {
        return $this->isPublic;
    }
    
    public function setVisitorTranslationKey($key)
    {
        $this->visitorTranslationKey = $key;
    }
    
    public function getVisitorTranslationKey()
    {
        return $this->visitorTranslationKey;
    }

    public function setCollaboratorTranslationKey($key)
    {
        $this->collaboratorTranslationKey = $key;
    }

    public function getCollaboratorTranslationKey()
    {
        return $this->collaboratorTranslationKey;
    }

    public function setManagerTranslationKey($key)
    {
        $this->managerTranslationKey = $key;
    }

    public function getManagerTranslationKey()
    {
        return $this->managerTranslationKey;
    }
    
    public function setType()
    {
        $this->type = $type;
    }
    
    public function getType()
    {
        return $this->type;
    }
    
    public function check()
    {      
        if ($this->workspaceType != self::TYPE_SIMPLE && $this->workspaceType != self::TYPE_AGGREGATOR)
        {
            throw new ClarolineException("Unknown workspace type '{$this->workspaceType}'");
        }
        
        if (! is_string($this->workspaceName) || 0 === strlen($this->workspaceName))
        {
            throw new ClarolineException('Workspace name must be a non empty string');
        }
    }
}