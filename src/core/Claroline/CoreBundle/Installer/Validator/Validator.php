<?php

namespace Claroline\CoreBundle\Installer\Validator;

use Claroline\CoreBundle\AbstractType\ClarolinePlugin;
use Claroline\CoreBundle\AbstractType\ClarolineTool;
use Claroline\CoreBundle\AbstractType\ClarolineExtension;
use Claroline\CoreBundle\Installer\Validator\Checker\CommonChecker;
use Claroline\CoreBundle\Installer\Validator\Checker\ToolChecker;
use Claroline\CoreBundle\Installer\Validator\Checker\ExtensionChecker;

class Validator
{
    private $commonChecker;
    private $extensionChecker;
    private $toolChecker;
    
    public function __construct(
        CommonChecker $commonChecker,
        ToolChecker $toolChecker,
        ExtensionChecker $extensionChecker
    )
    {
        $this->commonChecker = $commonChecker;
        $this->toolChecker = $toolChecker;
        $this->extensionChecker = $extensionChecker;
    }
    
    public function setCommonChecker(CommonChecker $checker)
    {
        $this->commonChecker = $checker;
    }

    public function setToolChecker(ToolChecker $checker)
    {
        $this->toolChecker = $checker;
    }
    
    public function setExtensionChecker(ExtensionChecker $checker)
    {
        $this->extensionChecker = $checker;
    }
    
    public function validate(ClarolinePlugin $plugin)
    {
        $this->commonChecker->check($plugin);
        
        if ($plugin instanceof ClarolineTool)
        {
            $this->toolChecker->check($plugin);
        }
        elseif ($plugin instanceof ClarolineExtension)
        {
            $this->extensionChecker->check($plugin);
        }
    }
}