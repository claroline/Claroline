<?php

namespace Invalid\UnloadableResourceClass2;

use Claroline\CoreBundle\Library\PluginBundle;

/*
 * Invalid because it declares a resource but doesn't extend abstract resource
 */
class InvalidUnloadableResourceClass2 extends PluginBundle
{
}