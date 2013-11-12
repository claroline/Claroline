<?php

namespace Claroline\WebInstaller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Installation\Settings\FirstAdminSettings;
use Claroline\CoreBundle\Library\Security\PlatformRoles;

class Installer
{
    private $adminSettings;
    private $writer;
    private $kernelFile;
    private $kernelClass;

    public function __construct(
        FirstAdminSettings $adminSettings,
        Writer $writer,
        $kernelFile,
        $kernelClass
    )
    {
        $this->adminSettings = $adminSettings;
        $this->writer = $writer;
        $this->kernelFile = $kernelFile;
        $this->kernelClass = $kernelClass;
    }

    public function install()
    {
        require_once $this->kernelFile;

        $kernel = new $this->kernelClass('prod', false);
        $kernel->boot();

        $refresher = $kernel->getContainer()->get('claroline.installation.refresher');
        $refresher->installAssets();

        $executor = $kernel->getContainer()->get('claroline.installation.operation_executor');
        $executor->execute();

        $userManager = $kernel->getContainer()->get('claroline.manager.user_manager');
        $user = new User();
        $user->setFirstName($this->adminSettings->getFirstName());
        $user->setLastName($this->adminSettings->getLastName());
        $user->setUsername($this->adminSettings->getUsername());
        $user->setPlainPassword($this->adminSettings->getPassword());
        $user->setMail($this->adminSettings->getEmail());
        $userManager->createUserWithRole($user, PlatformRoles::ADMIN);

        $refresher->dumpAssets('prod');
        $refresher->clearCache();

        $this->writer->writeInstallFlag();
    }
}
