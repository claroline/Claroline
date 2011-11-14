<?php

namespace Claroline\CommonBundle\Library\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Extendable extends Annotation
{
    public $discriminatorColumn;
}