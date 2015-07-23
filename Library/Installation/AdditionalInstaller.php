<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

use Claroline\CoreBundle\Library\Installation\Updater\MaintenancePageUpdater;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Symfony\Bundle\SecurityBundle\Command\InitAclCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AdditionalInstaller extends BaseInstaller
{
    public function preInstall()
    {
        $this->setLocale();
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        $maintenanceUpdater = new Updater\WebUpdater($this->container->getParameter('kernel.root_dir'));
        $maintenanceUpdater->preUpdate();

        $this->setLocale();

        switch (true) {
            case version_compare($currentVersion, '2.0', '<') && version_compare($targetVersion, '2.0', '>='):
                $updater = new Updater\Updater020000($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
            case version_compare($currentVersion, '2.9.0', '<'):
                $updater = new Updater\Updater020900($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
            case version_compare($currentVersion, '3.0.0', '<'):
                $updater = new Updater\Updater030000($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
            case version_compare($currentVersion, '3.8.0', '<'):
                $updater = new Updater\Updater030800($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
            case version_compare($currentVersion, '4.8.0', '<'):
                $updater = new Updater\Updater040800($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
            case version_compare($currentVersion, '5.0.0', '<'):
                $updater = new Updater\Updater050000($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        $this->setLocale();

        switch (true) {
            case version_compare($currentVersion, '2.0', '<')  && version_compare($targetVersion, '2.0', '>='):
                $updater = new Updater\Updater020000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.1.2', '<'):
                $updater = new Updater\Updater020102($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.1.5', '<'):
                $this->log('Creating acl tables if not present...');
                $command = new InitAclCommand();
                $command->setContainer($this->container);
                $command->run(new ArrayInput(array(), new NullOutput()));
            case version_compare($currentVersion, '2.2.0', '<'):
                $updater = new Updater\Updater020200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.3.1', '<'):
                $updater = new Updater\Updater020301($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.3.4', '<'):
                $updater = new Updater\Updater020304($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.5.0', '<'):
                $updater = new Updater\Updater020500($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.8.0', '<'):
                $updater = new Updater\Updater020800($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.9.0', '<'):
                $updater = new Updater\Updater020900($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.10.0', '<'):
                $updater = new Updater\Updater021000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.11.0', '<'):
                $updater = new Updater\Updater021100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.12.0', '<'):
                $updater = new Updater\Updater021200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.12.1', '<'):
                $updater = new Updater\Updater021201($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.14.0', '<'):
                $updater = new Updater\Updater021400($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.14.1', '<'):
                $updater = new Updater\Updater021401($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.16.0', '<'):
                $updater = new Updater\Updater021600($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.16.2', '<'):
                $updater = new Updater\Updater021602($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '2.16.4', '<'):
                $updater = new Updater\Updater021604($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.0.0', '<'):
                $updater = new Updater\Updater030000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.1.0', '<'):
                $updater = new Updater\Updater030100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.2.0', '<'):
                $updater = new Updater\Updater030200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.3.0', '<'):
                $updater = new Updater\Updater030300($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.4.0', '<'):
                $updater = new Updater\Updater030400($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.5.2', '<'):
                $updater = new Updater\Updater030502($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.6.1', '<'):
                $updater = new Updater\Updater030601($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.7.0', '<'):
                $updater = new Updater\Updater030700($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '3.8.0', '<'):
                $updater = new Updater\Updater030800($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.1.0', '<'):
                $updater = new Updater\Updater040100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.2.0', '<'):
                $updater = new Updater\Updater040200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.5.0', '<'):
                $updater = new Updater\Updater040500($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.8.0', '<'):
                $updater = new Updater\Updater040800($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.8.1', '<'):
                $updater = new Updater\Updater040801($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.8.4', '<'):
                $updater = new Updater\Updater040804($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.10.0', '<'):
                $updater = new Updater\Updater041000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '4.11.1', '<'):
                $updater = new Updater\Updater041101($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '5.0.3', '<'):
                $updater = new Updater\Updater050003($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '5.1.8', '<'):
                $updater = new Updater\Updater050108($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            case version_compare($currentVersion, '6.0.0', '<'):
                $updater = new Updater\Updater060000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
        }
    }

    private function setLocale()
    {
        $ch = $this->container->get('claroline.config.platform_config_handler');
        $locale = $ch->getParameter('locale_language');
        $translator = $this->container->get('translator');
        $translator->setLocale($locale);
    }
}
