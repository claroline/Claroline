<?php

namespace Claroline\AgendaBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineAgendaInstaller extends AdditionalInstaller
{
    public function hasFixtures(): bool
    {
        return true;
    }
}
