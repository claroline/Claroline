<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\UnloadableBundleClass5;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

abstract class InvalidUnloadableBundleClass5 extends DistributionPluginBundle
{
    /*
     * Invalid because it cannot be instantiated
     */
}
