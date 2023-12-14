<?php

namespace Icap\LessonBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class IcapLessonInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}
