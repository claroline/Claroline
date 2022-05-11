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

use Claroline\CommunityBundle\Installation\Updater\Updater130500;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class ClarolineCommunityInstaller extends BaseInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.5.0' => Updater130500::class,
        ];
    }

    public function hasMigrations(): bool
    {
        return false;
    }
}
