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

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\TransferBundle\Installation\Updater\Updater150000;

class ClarolineTransferInstaller extends AdditionalInstaller
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
