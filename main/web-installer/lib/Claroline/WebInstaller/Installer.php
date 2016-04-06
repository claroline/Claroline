<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\WebInstaller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Installation\Settings\FirstAdminSettings;
use Claroline\CoreBundle\Library\Security\PlatformRoles;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class Installer
{
    private $adminSettings;
    private $writer;
    private $kernelFile;
    private $kernelClass;
    private $appDir;
    private $hasSucceeded = false;
    private $logFilename = null;

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
        $this->appDir = dirname($kernelFile);
    }

    public function install()
    {
        $this->logFilename = 'install-' . time() . '.log';
        $logFile = $this->appDir . '/logs/' . $this->logFilename;
        $output = new StreamOutput(fopen($logFile, 'a'));

        try {
            // preventive clear in case the installer is launched twice
            $this->clearCache($output);

            require_once $this->kernelFile;

            $kernel = new $this->kernelClass('prod', false);
            $kernel->boot();
            $container = $kernel->getContainer();
            $this->launchInstaller($container, $output);
            $this->createAdminUser($container, $output);
            //with command line... but it's broken. The other one works.
            //exec('php ' . $container->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . 'console assetic:dump');
            $refresher = $container->get('claroline.installation.refresher');
            $refresher->setOutput($output);
            $refresher->installAssets();
            $refresher->dumpAssets('prod');
            $this->writer->writeInstallFlag();
            $this->hasSucceeded = true;
        } catch (\Exception $ex) {
            $output->writeln('[ERROR] An exception has been thrown during installation');
            $output->writeln('Message: ' . $ex->getMessage());
            $output->writeln('Trace: ' . $ex->getTraceAsString());
        }
    }

    public function hasSucceeded()
    {
        return $this->hasSucceeded;
    }

    public function getLogFilename()
    {
        return $this->logFilename;
    }

    private function clearCache(OutputInterface $output)
    {
        $output->writeln('Clearing the cache...');

        if (is_dir($directory = $this->appDir . '/cache')) {
            $fileSystem = new Filesystem();
            $cacheIterator = new \DirectoryIterator($directory);

            foreach ($cacheIterator as $item) {
                if (!$item->isDot()) {
                    $fileSystem->remove($item->getPathname());
                }
            }
        }
    }

    private function launchInstaller(ContainerInterface $container, OutputInterface $output)
    {
        /** @var \Claroline\CoreBundle\Library\Installation\PlatformInstaller $installer */
        $installer = $container->get('claroline.installation.platform_installer');
        $installer->setOutput($output);
        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
        );
        $logger = new ConsoleLogger($output, $verbosityLevelMap);
        $installer->setLogger($logger);
        $output->writeln('Installing the platform from composer...');
        $installer->updateFromComposerInfo();
    }

    private function createAdminUser(ContainerInterface $container, OutputInterface $output)
    {
        $output->writeln('Creating first admin user...');
        $userManager = $container->get('claroline.manager.user_manager');
        $user = new User();
        $user->setFirstName($this->adminSettings->getFirstName());
        $user->setLastName($this->adminSettings->getLastName());
        $user->setUsername($this->adminSettings->getUsername());
        $user->setPlainPassword($this->adminSettings->getPassword());
        $user->setMail($this->adminSettings->getEmail());
        $userManager->createUserWithRole($user, PlatformRoles::ADMIN);
    }
}
