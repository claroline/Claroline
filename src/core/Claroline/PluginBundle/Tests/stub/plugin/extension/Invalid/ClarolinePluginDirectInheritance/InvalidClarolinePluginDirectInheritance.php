<?php

namespace Invalid\ClarolinePluginDirectInheritance;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

/**
 * Invalid because it doesn't extend one of the ClarolinePlugin 
 * sub types (extension, application, tool).
 */
class InvalidClarolinePluginDirectInheritance extends ClarolinePlugin
{
}