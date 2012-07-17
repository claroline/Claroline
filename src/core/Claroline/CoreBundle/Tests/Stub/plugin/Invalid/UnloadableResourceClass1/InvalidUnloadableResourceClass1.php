<?php

namespace Invalid\UnloadableResourceClass1;

use Claroline\CoreBundle\Library\PluginBundle;

/*
 * Invalid because it declares a resource but doesn't provide the corresponding class.
 */
class InvalidUnloadableResourceClass1 extends PluginBundle
{
}