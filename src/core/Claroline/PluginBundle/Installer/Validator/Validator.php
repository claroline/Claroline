<?php

namespace Claroline\PluginBundle\Installer\Validator;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;
use Claroline\PluginBundle\Installer\Validator\Checker\CommonChecker;
use Claroline\PluginBundle\Installer\Validator\Checker\ExtensionChecker;
use Claroline\PluginBundle\Installer\Validator\Checker\ApplicationChecker;
use Claroline\PluginBundle\Installer\Validator\Checker\ToolChecker;

class Validator
{
    private $commonChecker;
    private $extensionChecker;
    private $applicationChecker;
    private $toolChecker;
    
    public function __construct(
        CommonChecker $commonChecker,
        ExtensionChecker $extensionChecker,
        ApplicationChecker $applicationChecker,
        ToolChecker $toolChecker
    )
    {
        $this->commonChecker = $commonChecker;
        $this->extensionChecker = $extensionChecker;
        $this->applicationChecker = $applicationChecker;
        $this->toolChecker = $toolChecker;
    }
    
    public function setCommonChecker(CommonChecker $checker)
    {
        $this->commonChecker = $checker;
    }
    
    public function setExtensionChecker(ExtensionChecker $checker)
    {
        $this->extensionChecker = $checker;
    }

    public function setApplicationChecker(ApplicationChecker $checker)
    {
        $this->applicationChecker = $checker;
    }

    public function setToolChecker(ToolChecker $checker)
    {
        $this->toolChecker = $checker;
    }
    
    public function validate(ClarolinePlugin $plugin)
    {
        $this->commonChecker->check($plugin);
        
        if ($plugin instanceof \Claroline\PluginBundle\AbstractType\ClarolineExtension)
        {
            $this->extensionChecker->check($plugin);
        }
        elseif ($plugin instanceof \Claroline\PluginBundle\AbstractType\ClarolineApplication)
        {
            $this->applicationChecker->check($plugin);
        }
        elseif ($plugin instanceof \Claroline\PluginBundle\AbstractType\ClarolineTool)
        {
            $this->toolChecker->check($plugin);
        }
    }
}