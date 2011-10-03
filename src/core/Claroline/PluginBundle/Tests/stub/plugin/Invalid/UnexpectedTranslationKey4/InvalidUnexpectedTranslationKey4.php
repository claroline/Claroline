<?php

namespace Invalid\UnexpectedTranslationKey4;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedTranslationKey4 extends ClarolinePlugin
{
    public function getDescriptionTranslationKey()
    {
        return new \DOMDocument();
    }
}