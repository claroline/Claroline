<?php

namespace Invalid\UnexpectedTranslationKey4;

use Claroline\CoreBundle\AbstractType\ClarolineExtension;

class InvalidUnexpectedTranslationKey4 extends ClarolineExtension
{
    public function getDescriptionTranslationKey()
    {
        return new \DOMDocument();
    }
}