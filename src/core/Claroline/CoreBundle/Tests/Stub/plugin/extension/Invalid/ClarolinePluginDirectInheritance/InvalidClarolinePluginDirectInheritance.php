<?php

namespace Invalid\ClarolinePluginDirectInheritance;

use Claroline\CoreBundle\Plugin\ClarolinePlugin;

/**
 * Invalid because it doesn't extend one of the ClarolinePlugin 
 * sub types (extension, application, tool).
 */
class InvalidClarolinePluginDirectInheritance extends ClarolinePlugin
{
}