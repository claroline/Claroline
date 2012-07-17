<?php

namespace Invalid\UnexpectedResourceClassType1;

use Claroline\CoreBundle\Library\PluginBundle;

/**
 * Invalid because a resource it declares doesn't extend core AbstractResource
 */
class InvalidUnexpectedResourceClassType1 extends PluginBundle
{
}