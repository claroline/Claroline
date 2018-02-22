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

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Psr\Log\LogLevel;
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
        $dataWebDir = $this->container->getParameter('claroline.param.data_web_dir');
        $fileSystem = $this->container->get('filesystem');
        $publicFilesDir = $this->container->getParameter('claroline.param.public_files_directory');

        if (!$fileSystem->exists($dataWebDir)) {
            $this->log('Creating symlink to public directory of files directory in web directory...');
            $fileSystem->symlink($publicFilesDir, $dataWebDir);
        } else {
            if (!is_link($dataWebDir)) {
                //we could remove it manually but it might be risky
                $this->log('Symlink from web/data to files/data could not be created, please remove your web/data folder manually', LogLevel::ERROR);
            } else {
                $this->log('Web folder symlinks validated...');
            }
        }

        try {
            $updater = new Updater\Updater100000($this->container);
            $updater->moveUploadsDirectory();
        } catch (\Exception $e) {
            $this->log($e->getMessage(), LogLevel::ERROR);
        }

        try {
            $updater = new Updater\Updater110000($this->container);
            $updater->lnPictureDirectory();
            $updater->lnPackageDirectory();
        } catch (\Exception $e) {
            $this->log($e->getMessage(), LogLevel::ERROR);
        }

        $maintenanceUpdater = new Updater\WebUpdater($this->container->getParameter('kernel.root_dir'));
        $maintenanceUpdater->preUpdate();

        $this->setLocale();

        switch (true) {
            case version_compare($currentVersion, '2.0', '<') && version_compare($targetVersion, '2.0', '>='):
                $updater = new Updater\Updater020000($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
                // no break
            case version_compare($currentVersion, '2.9.0', '<'):
                $updater = new Updater\Updater020900($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
                // no break
            case version_compare($currentVersion, '3.0.0', '<'):
                $updater = new Updater\Updater030000($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
                // no break
            case version_compare($currentVersion, '3.8.0', '<'):
                $updater = new Updater\Updater030800($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
                // no break
            case version_compare($currentVersion, '4.8.0', '<'):
                $updater = new Updater\Updater040800($this->container);
                $updater->setLogger($this->logger);
                $updater->preUpdate();
                // no break
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
            case version_compare($currentVersion, '2.0', '<') && version_compare($targetVersion, '2.0', '>='):
                $updater = new Updater\Updater020000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.1.2', '<'):
                $updater = new Updater\Updater020102($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.1.5', '<'):
                $this->log('Creating acl tables if not present...');
                $command = new InitAclCommand();
                $command->setContainer($this->container);
                $command->run(new ArrayInput([]), new NullOutput());
                // no break
            case version_compare($currentVersion, '2.2.0', '<'):
                $updater = new Updater\Updater020200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.3.1', '<'):
                $updater = new Updater\Updater020301($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.3.4', '<'):
                $updater = new Updater\Updater020304($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.5.0', '<'):
                $updater = new Updater\Updater020500($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.8.0', '<'):
                $updater = new Updater\Updater020800($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.9.0', '<'):
                $updater = new Updater\Updater020900($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.10.0', '<'):
                $updater = new Updater\Updater021000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.11.0', '<'):
                $updater = new Updater\Updater021100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.12.0', '<'):
                $updater = new Updater\Updater021200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.12.1', '<'):
                $updater = new Updater\Updater021201($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.14.0', '<'):
                $updater = new Updater\Updater021400($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.14.1', '<'):
                $updater = new Updater\Updater021401($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.16.0', '<'):
                $updater = new Updater\Updater021600($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.16.2', '<'):
                $updater = new Updater\Updater021602($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '2.16.4', '<'):
                $updater = new Updater\Updater021604($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.0.0', '<'):
                $updater = new Updater\Updater030000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.1.0', '<'):
                $updater = new Updater\Updater030100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.2.0', '<'):
                $updater = new Updater\Updater030200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.3.0', '<'):
                $updater = new Updater\Updater030300($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.5.2', '<'):
                $updater = new Updater\Updater030502($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.6.1', '<'):
                $updater = new Updater\Updater030601($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.7.0', '<'):
                $updater = new Updater\Updater030700($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '3.8.0', '<'):
                $updater = new Updater\Updater030800($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.1.0', '<'):
                $updater = new Updater\Updater040100($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.2.0', '<'):
                $updater = new Updater\Updater040200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.5.0', '<'):
                $updater = new Updater\Updater040500($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.8.0', '<'):
                $updater = new Updater\Updater040800($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.8.1', '<'):
                $updater = new Updater\Updater040801($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.8.4', '<'):
                $updater = new Updater\Updater040804($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.10.0', '<'):
                $updater = new Updater\Updater041000($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '4.11.1', '<'):
                $updater = new Updater\Updater041101($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '5.0.3', '<'):
                $updater = new Updater\Updater050003($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '5.1.8', '<'):
                $updater = new Updater\Updater050108($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '5.1.14', '<'):
                $updater = new Updater\Updater050114($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '6.3.0', '<'):
                $updater = new Updater\Updater060300($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '6.4.0', '<'):
                $updater = new Updater\Updater060400($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '6.5.0', '<'):
                $updater = new Updater\Updater060500($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '6.6.7', '<'):
                $updater = new Updater\Updater060607($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '6.7.0', '<'):
                $updater = new Updater\Updater060700($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '6.7.4', '<='):
                    $updater = new Updater\Updater060704($this->container);
                    $updater->setLogger($this->logger);
                    $updater->postUpdate();
                    // no break
            case version_compare($currentVersion, '6.8.0', '<'):
                $updater = new Updater\Updater060800($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '7.0.0', '<'):
                $updater = new Updater\Updater070000($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '8.0.0', '<'):
                $updater = new Updater\Updater080000($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '9.1.0', '<'):
                $updater = new Updater\Updater090100($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '9.2.0', '<'):
                $updater = new Updater\Updater090200($this->container);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '9.3.0', '<'):
                $updater = new Updater\Updater090300($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '10.0.0', '<'):
                $updater = new Updater\Updater100000($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '10.0.30', '<'):
                $updater = new Updater\Updater100030($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '10.2.0', '<'):
                $updater = new Updater\Updater100200($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '11.0.0', '<'):
                $updater = new Updater\Updater110000($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
            // no break
            case version_compare($currentVersion, '11.2.0', '<'):
                $updater = new Updater\Updater110200($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
                // no break
            case version_compare($currentVersion, '11.3.0', '<'):
                $updater = new Updater\Updater110300($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->postUpdate();
        }

        $termsOfServiceManager = $this->container->get('claroline.common.terms_of_service_manager');
        $termsOfServiceManager->sendDatas();

        $docUpdater = new Updater\DocUpdater($this->container);
        $docUpdater->updateDocUrl('http://doc.claroline.com');
    }

    public function end()
    {
        $this->container->get('claroline.installation.refresher')->installAssets();
        $this->log('Updating resource icons...');
        $this->container->get('claroline.manager.icon_set_manager')->setLogger($this->logger);
        $this->container->get('claroline.manager.icon_set_manager')->addDefaultIconSets();
    }

    private function setLocale()
    {
        $ch = $this->container->get('claroline.config.platform_config_handler');
        $locale = $ch->getParameter('locale_language');
        $translator = $this->container->get('translator');
        $translator->setLocale($locale);
    }
}
