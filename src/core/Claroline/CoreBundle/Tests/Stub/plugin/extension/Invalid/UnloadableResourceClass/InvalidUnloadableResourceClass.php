<?php

namespace Invalid\UnloadableResourceClass;

use Claroline\CoreBundle\Library\Plugin\ClarolineExtension;

/*
 * Invalid because it declares a resource but doesn't provide the corresponding class.
 */
class InvalidUnloadableResourceClass extends ClarolineExtension
{
}