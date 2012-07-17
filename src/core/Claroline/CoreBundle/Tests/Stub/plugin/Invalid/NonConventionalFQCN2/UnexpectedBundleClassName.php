<?php

namespace Invalid\NonConventionalFQCN2;

use Claroline\CoreBundle\Library\PluginBundle;

class UnexpectedBundleClassName extends PluginBundle
{
    /**
     * Invalid because the class name is not the concatenation of the vendor and bundle names
     */
}