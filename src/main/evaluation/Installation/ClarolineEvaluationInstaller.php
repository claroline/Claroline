<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineEvaluationInstaller extends AdditionalInstaller
{
    public function hasFixtures(): bool
    {
        return true;
    }

    public function hasMigrations(): bool
    {
        return false;
    }
}
