<?php

namespace Claroline\CoreBundle\Library\Workspace;

use \RuntimeException;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;

class Configuration
{
    const TYPE_SIMPLE = 'Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace';
    const TYPE_AGGREGATOR = 'Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace';

    private $workspaceType;
    private $workspaceName;
    private $workspaceCode;
    private $isPublic;
    private $type;
    private $visitorTranslationKey;
    private $collaboratorTranslationKey;
    private $managerTranslationKey;

    public function __construct()
    {
        $this->workspaceType = self::TYPE_SIMPLE;
        $this->isPublic = true;
        $this->type = AbstractWorkspace::STANDARD;
        $this->visitorTranslationKey = 'visitor';
        $this->collaboratorTranslationKey = 'collaborator';
        $this->managerTranslationKey = 'manager';
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

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
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

    public function check()
    {
        if ($this->workspaceType != self::TYPE_SIMPLE && $this->workspaceType != self::TYPE_AGGREGATOR) {
            throw new RuntimeException("Unknown workspace type '{$this->workspaceType}'");
        }

        if (!is_string($this->workspaceName) || 0 === strlen($this->workspaceName)) {
            throw new RuntimeException('Workspace name must be a non empty string');
        }
    }

    public function setWorkspaceCode($workspaceCode)
    {
        $this->workspaceCode = $workspaceCode;
    }

    public function getWorkspaceCode()
    {
        return $this->workspaceCode;
    }
}