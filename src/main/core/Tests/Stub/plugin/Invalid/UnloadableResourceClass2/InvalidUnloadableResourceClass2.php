<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\UnloadableResourceClass2;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

/*
 * Invalid because it declares a resource but doesn't extend abstract resource
 */
class InvalidUnloadableResourceClass2 extends DistributionPluginBundle
{
}
