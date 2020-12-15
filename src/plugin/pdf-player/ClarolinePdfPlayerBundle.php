<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PdfPlayerBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;

class ClarolinePdfPlayerBundle extends DistributionPluginBundle
{
    public function hasMigrations()
    {
        return false;
    }
}
