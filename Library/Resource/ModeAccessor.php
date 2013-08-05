<?php

namespace Claroline\CoreBundle\Library\Resource;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.resource.mode_accessor")
 *
 * Simple data class holding the resource mode flag. If the mode is set to
 * "path", resources are expected to be rendered within a minimal layout, as
 * opposed to the usual full page mode.
 */
class ModeAccessor
{
    private $isPathMode = false;

    /**
     * @param boolean $isPathMode
     */
    public function setPathMode($isPathMode)
    {
        $this->isPathMode = $isPathMode;
    }

    /**
     *
     * @return boolean
     */
    public function isPathMode()
    {
        return $this->isPathMode;
    }
}
