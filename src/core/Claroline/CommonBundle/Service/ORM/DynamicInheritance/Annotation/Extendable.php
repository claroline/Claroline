<?php

namespace Claroline\CommonBundle\Service\ORM\DynamicInheritance\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Extendable extends Annotation
{
    public $discriminatorColumn;
}