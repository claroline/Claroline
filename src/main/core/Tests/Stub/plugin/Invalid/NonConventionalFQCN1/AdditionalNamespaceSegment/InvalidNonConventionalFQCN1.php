<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Invalid\NonConventionalFQCN1\AdditionalNamespaceSegment;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class InvalidNonConventionalFQCN1 extends DistributionPluginBundle
{
    /*
     * Invalid because it adds an extra segment to the FQCN convention.
     */
}
