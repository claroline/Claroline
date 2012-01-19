<?php

namespace Invalid\UnexpectedPluginLocation;

use Claroline\CoreBundle\AbstractType\ClarolineTool;

/**
 * Invalid because tools must be located in the "plugin/tool" directory.
 */
class InvalidUnexpectedPluginLocation extends ClarolineTool
{
}