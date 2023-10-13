<?php

namespace Claroline\AppBundle\Component\Tool;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\AppBundle\Component\Context\ContextualInterface;

interface ToolInterface extends ComponentInterface, ContextualInterface
{
    /**
     * Gets the lists of custom rights for the tool.
     *
     * @return array - an array of strings containing the rights names.
     */
    public static function getAdditionalRights(): array;
}
