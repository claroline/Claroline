<?php

namespace Invalid\UnexpectedTranslationKey4;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedTranslationKey4 extends PluginBundle
{
    public function getDescriptionTranslationKey()
    {
        return new \DOMDocument();
    }
}