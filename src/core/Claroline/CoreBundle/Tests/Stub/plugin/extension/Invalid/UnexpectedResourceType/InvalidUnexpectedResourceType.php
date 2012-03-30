<?php

namespace Invalid\UnexpectedResourceType;

use Claroline\CoreBundle\Library\Plugin\ClarolineExtension;

/**
 * Invalid because a resource it declares doesn't extend core AbstractResource
 */
class InvalidUnexpectedResourceType extends ClarolineExtension
{
}