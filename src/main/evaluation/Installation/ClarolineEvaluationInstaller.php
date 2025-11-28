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

use Claroline\EvaluationBundle\Installation\Updater\Updater150000;
use Claroline\EvaluationBundle\Installation\Updater\Updater150010;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineEvaluationInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '15.0.0' => Updater150000::class,
            '15.0.10' => Updater150010::class,
        ];
    }

    public function hasMigrations(): bool
    {
        return true;
    }
}
