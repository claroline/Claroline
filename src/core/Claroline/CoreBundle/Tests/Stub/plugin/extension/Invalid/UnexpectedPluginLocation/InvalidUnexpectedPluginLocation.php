<?php

namespace Invalid\UnexpectedPluginLocation;

use Claroline\CoreBundle\Library\Plugin\ClarolineTool;

/**
 * Invalid because tools must be located in the "plugin/tool" directory.
 */
class InvalidUnexpectedPluginLocation extends ClarolineTool
{
}