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
use Claroline\CommunityBundle\Installation\Updater\Updater130700;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineCommunityInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.5.0' => Updater130500::class,
            '13.7.0' => Updater130700::class,
        ];
    }
}
