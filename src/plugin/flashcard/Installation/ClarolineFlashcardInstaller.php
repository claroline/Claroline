<?php

namespace Claroline\FlashcardBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineFlashcardInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}
