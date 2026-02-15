<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Installation;

use Claroline\CommunityBundle\Installation\Updater\Updater150000;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineCommunityInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '15.0.0' => Updater150000::class,
        ];
    }

    public function hasMigrations(): bool
    {
        return true;
    }
}
