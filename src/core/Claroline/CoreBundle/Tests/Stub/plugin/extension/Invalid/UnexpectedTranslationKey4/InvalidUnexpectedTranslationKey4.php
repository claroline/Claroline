<?php

namespace Invalid\UnexpectedTranslationKey4;

use Claroline\CoreBundle\Plugin\ClarolineExtension;

class InvalidUnexpectedTranslationKey4 extends ClarolineExtension
{
    public function getDescriptionTranslationKey()
    {
        return new \DOMDocument();
    }
}