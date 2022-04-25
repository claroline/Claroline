<?php

namespace Claroline\TagBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\TagBundle\Installation\Updater\Updater130500;

class ClarolineTagInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.5.0' => Updater130500::class,
        ];
    }
}
