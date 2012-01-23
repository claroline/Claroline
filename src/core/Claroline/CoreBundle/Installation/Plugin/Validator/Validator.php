<?php

namespace Claroline\CoreBundle\Installation\Plugin\Validator;

use Claroline\CoreBundle\Plugin\ClarolinePlugin;
use Claroline\CoreBundle\Plugin\ClarolineTool;
use Claroline\CoreBundle\Plugin\ClarolineExtension;
use Claroline\CoreBundle\Installation\Plugin\Validator\Checker\CommonChecker;
use Claroline\CoreBundle\Installation\Plugin\Validator\Checker\ToolChecker;
use Claroline\CoreBundle\Installation\Plugin\Validator\Checker\ExtensionChecker;

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