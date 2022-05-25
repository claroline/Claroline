<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TransferBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Claroline\TransferBundle\Installation\Updater\Updater130300;
use Claroline\TransferBundle\Installation\Updater\Updater130500;

class ClarolineTransferInstaller extends BaseInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.3.0' => Updater130300::class,
            '13.5.0' => Updater130500::class,
        ];
    }
}
