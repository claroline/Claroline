<?php

namespace Invalid\NonConventionalFQCN2;

use Claroline\CoreBundle\Plugin\ClarolineExtension;

class UnexpectedBundleClassName extends ClarolineExtension
{
    /**
     * Invalid because the class name is not the concatenation of the vendor and bundle names
     */
}