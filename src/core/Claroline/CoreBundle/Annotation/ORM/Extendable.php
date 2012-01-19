<?php

namespace Claroline\CoreBundle\Annotation\ORM;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 */
class Extendable extends Annotation
{
    public $discriminatorColumn;
}