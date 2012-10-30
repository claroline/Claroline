<?php

namespace Claroline\CoreBundle\Library\Widget;

class Manager
{
    /**
     *
     * @param type $displayConfig
     *
     */
    public function getWidgetParameters($displayConfig)
    {
        $saveConfig = $displayConfig;
        //childid, islocked, isvisible

        while ($displayConfig != null){
            $displayConfig = $displayConfig->getParent();

            if (null !== $displayConfig && $displayConfig->isLocked()){
                $saveConfig->setVisible($displayConfig->isVisible());
                $saveConfig->setLock($displayConfig->isLocked());
            }
        }

        return $saveConfig;
    }
}
