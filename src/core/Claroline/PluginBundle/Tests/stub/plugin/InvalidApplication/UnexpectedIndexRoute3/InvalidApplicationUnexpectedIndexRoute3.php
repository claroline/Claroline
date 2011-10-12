<?php

namespace InvalidApplication\UnexpectedIndexRoute3;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class InvalidApplicationUnexpectedIndexRoute3 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array(
            new ApplicationLauncher('route_test', 'translation_test', array('ROLE_TEST'))
        );
    }
    
    /**
     * Returned string value cannot exceed 255 characters.
     */
    public function getIndexRoute()
    {
        $tooLong = '';
        
        for ($i = 0; $i < 100; ++$i)
        {
            $tooLong .= 'XXXX';
        }
        
        return $tooLong;
    }
}
