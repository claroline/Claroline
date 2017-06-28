<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\UnloadableResourceClass1;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

/*
 * Invalid because it declares a resource but doesn't provide the corresponding class.
 */
class InvalidUnloadableResourceClass1 extends DistributionPluginBundle
{
}
