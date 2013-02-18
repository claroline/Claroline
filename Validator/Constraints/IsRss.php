<?php

namespace Claroline\RssReaderBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class IsRss extends Constraint
{
    public $message = 'invalid_rss_url';

    public function validateBy()
    {
        return get_class($this).'Validator';
    }
}

