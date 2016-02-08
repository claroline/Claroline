<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Recorder;
use Claroline\CoreBundle\Manager\IniFileManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Service("claroline.manager.bundle_manager")
 */
class BundleManager
{
    private $om;
    private $bundleRepository;
    private $pluginRepository;
    private $kernelRootDir;
    private $configHandler;
    private $vendorDir;
    private $iniFileManager;
    private $installer;
    private $refresher;

    /**
     * @InjectParams({
     *      "om"             = @Inject("claroline.persistence.object_manager"),
     *      "kernelRootDir"  = @Inject("%kernel.root_dir%"),
     *      "configHandler"  = @Inject("claroline.config.platform_config_handler"),
     *      "vendorDir"      = @Inject("%claroline.param.vendor_directory%"),
     *      "iniFileManager" = @Inject("claroline.manager.ini_file_manager"),
     *      "installer"      = @Inject("claroline.installation.platform_installer"),
     *      "refresher"      = @Inject("claroline.installation.refresher")
     * })
     */
    public function __construct(
        ObjectManager $om,
        $kernelRootDir,
        PlatformConfigurationHandler $configHandler,
        $vendorDir,
        IniFileManager $iniFileManager,
        $installer,
        $refresher
    )
    {
        $this->om               = $om;
        $this->bundleRepository = $om->getRepository('ClarolineCoreBundle:Bundle');
        $this->pluginRepository = $om->getRepository('ClarolineCoreBundle:Plugin');
        $this->kernelRootDir    = $kernelRootDir;
        $this->configHandler    = $configHandler;
        $this->vendorDir        = $vendorDir;
        $this->iniFileManager   = $iniFileManager;
        $this->installer        = $installer;
        $this->refresher        = $refresher;
    }

    public function getBundle($bundle)
    {
        return $this->bundleRepository->findOneByName($bundle);
    }

    public function getInstalled()
    {
        return $this->bundleRepository->findAll();
    }

    /**
     * Get a list of uninstalled bundle.
     *
     * @param $bundles the list of available bundle fetched from the server
     */
    public function getUninstalledFromServer($bundles)
    {
        $installed = $this->getInstalled();
        $uninstalled = array();

        foreach ($bundles as $fetchedBundle) {
            $found = false;
            foreach ($installed as $bundle) {
                if ($bundle->getName() === $fetchedBundle->name) {
                    $found = true;
                }
            }

            if (!$found) $uninstalled[] = $fetchedBundle;
        }

        return $uninstalled;
    }

    public function getCoreBundleVersion()
    {
        $core = $this->getBundle('CoreBundle');

        return $core->getVersion();
    }

    public function installRemoteBundle($bundle, $date = null)
    {
        //step 1, get the archive from the remote server
        $zipFile = $this->fetchLastInstallableFromRemote($bundle);

        $this->installBundle($bundle, $zipFile, $date);
    }

    public function fetchLastInstallableFromRemote($bundle)
    {
        $api = $this->configHandler->getParameter('repository_api');
        $url = $api . "/bundle/$bundle/coreVersion/{$this->getCoreBundleVersion()}/download";

        if ($this->configHandler->getParameter('use_repository_test')) {
            $url .= '/test';
        }

        $ch = curl_init();
        $zipFile = sys_get_temp_dir() . '/' . uniqid() . '.zip';
        curl_setopt($ch, CURLOPT_URL, $url);
        $file = fopen($zipFile, "w+");;
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_exec($ch);
        curl_close($ch);
        fclose($file);

        return $zipFile;
    }

    public function getBundleLastInstallableVersion($bundle, $coreVersion)
    {
        $api = $this->configHandler->getParameter('repository_api');
        $url = $api . "/bundle/$bundle/coreVersion/{$this->getCoreBundleVersion()}/installable";

        if ($this->configHandler->getParameter('use_repository_test')) {
            $url .= '/test';
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        $o = json_decode($data);

        return $o->tag;
    }

    public function installBundle($bundle, $zipFile, $date = null)
    {
        $logFile = $this->getLogFile();
        if ($date) $logFile = $logFile . '-' . $date;
        $logFile .= '.log';
        @unlink($logFile);
        $fileLogger = new \Monolog\Logger('package.update');
        $fileLogger->pushHandler(new \Monolog\Handler\StreamHandler($logFile));
        $fileLogger->addInfo('Downloading archive...');
        $version = $this->getBundleLastInstallableVersion($bundle, $this->getCoreBundleVersion());
        $fs = new FileSystem();

        //extract and unzip in the correct directory
        $extractPath = sys_get_temp_dir() . '/' . uniqid();
        $zip = new \ZipArchive();
        $zip->open($zipFile);
        $fileLogger->addInfo("Extraction...");
        $zip->extractTo($extractPath);

        //rename the $extractPath root (it currently has -version at the end)
        $iterator = new \DirectoryIterator($extractPath);

        foreach ($iterator as $el) {
            //there should be only one directory so...
            if ($el->isDir() && !$el->isDot()) {
                $parts = explode('-', $el->getBaseName());
                $fs->rename($el->getPathName(), $extractPath . '/' . $parts[0]);
            }
        }

        //move the source where they should be
        $composer = $extractPath . "/$bundle/composer.json";
        $json = file_get_contents($composer);
        $data = json_decode($json);
        $targetDir = 'target-dir';
        $parts = explode('/', $data->$targetDir);
        $newPath = $this->vendorDir . '/' . $data->name . '/' . $data->$targetDir;
        $vendor = $parts[0];
        $baseParts = explode('Bundle', $parts[1]);
        $baseName = $baseParts[0];
        $fileLogger->addInfo("Removing old sources...");
        $fs->rmdir($newPath, true);
        $fileLogger->addInfo("Copying sources from temporary directory...");
        $fs->copyDir($extractPath . "/$bundle", $newPath);
        //then we update the autoloader
        $parts = explode('/', $data->name);
        $vname = $parts[0];
        $bname = $parts[1];
        $bundleType = $data->type;
        $updatedTarget = str_replace('/', '\\', $data->$targetDir);
        $fqcn = $updatedTarget . '\\' . str_replace('\\', '', $updatedTarget);
        $fileLogger->addInfo("Updating vendor/composer/autoload_namespace.php...");
        $this->updateAutoload($vendor, $baseName, $vname, $bname);
        $fileLogger->addInfo("Updating app/config/bundle.ini...");
        $this->updateIniFile($vendor, $baseName);
        $fileLogger->addInfo("Generating app/config/operations.xml...");
        $this->generateUniqueOperationFile($bundle, $version, $bundleType, $fqcn, $logFile);
        //We need a different process to execute the update as the new sources were
        //not loaded by php yet.
        //It's much easier than trying to load/refresh everything.
        //TODO: use Sf2 proces library to avoid mistakes
        //sanitize this
        $executor = $this->kernelRootDir . '/../vendor/claroline/core-bundle/Library/Installation/scripts/operation_executor.php';
        $phpErrors = $this->kernelRootDir . "/logs/php_errors_{$date}.log";
        exec("php $executor $date > $phpErrors");
    }

    public function getLogFile()
    {
        return $this->kernelRootDir . "/logs/update";
    }

    public function getRefreshLog()
    {
        return $this->kernelRootDir . "/logs/refresh";
    }

    public function executeOperationFile($date = null)
    {
        $logFile = $this->getLogFile();
        if ($date) $logFile = $logFile . '-' . $date;
        $logFile .= '.log';
        $output = new StreamOutput(fopen($logFile, 'a', false));

        $verbosityLevelMap = array(
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
        );
        $consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);

        $this->installer->setLogger($consoleLogger);
        $this->refresher->setOutput($output);
        $this->installer->installFromOperationFile();
        $this->refresher->installAssets();
        $this->refresher->dumpAssets('prod');
        $logLine = "Done !\n";
        file_put_contents($logFile, $logLine, FILE_APPEND);
    }

    public function updateIniFile($vendor, $bundle)
    {
        $iniFile = $this->kernelRootDir . '/config/bundles.ini';

        //update ini file
        $this->iniFileManager
            ->updateKey(
                $vendor . '\\' . $bundle . 'Bundle\\' . $vendor . $bundle . 'Bundle',
                true,
                $iniFile
            );
    }

    public function updateAutoload($ivendor, $ibundle, $vname, $bname)
    {
        //update namespace file
        $namespaces = $this->kernelRootDir . '/../vendor/composer/autoload_namespaces.php';
        $content = file_get_contents($namespaces);
        $lineToAdd = "\n    '{$ivendor}\\\\{$ibundle}Bundle' => array(\$vendorDir . '/{$vname}/{$bname}'),";

        if (!strpos($content, $lineToAdd)) {
            //add the correct line after corebundle...
            $content = str_replace(
                "/core-bundle'),",
                "/core-bundle'), {$lineToAdd}",
                $content
            );

            file_put_contents($namespaces, $content);
        }
    }

    /**
     * Here we generate a new operation.xml file.
     */
    public function generateUniqueOperationFile($bundle, $version, $bundleType, $fqcn, $logFile)
    {
        $entity = $this->getBundle($bundle);
        $isInstalled = false;

        if ($entity) {
            $oldVersion = $entity->getVersion();
            $isInstalled = true;
        }

        $operationFilePath = $this->kernelRootDir . "/config/operations.xml";
        //remove the old operation file if it exists (maybe it would be better to do a backup).
        @unlink($operationFilePath);

        $fileLogger = new \Monolog\Logger('package.update');
        $fileLogger->pushHandler(new \Monolog\Handler\StreamHandler($logFile));
        $operationHandler = new OperationHandler($operationFilePath, $fileLogger);

        //generating the operations.xml file
        $operation = new Operation(
            $isInstalled ? Operation::UPDATE: Operation::INSTALL,
            $fqcn,
            $bundleType === 'claroline-plugin' ? Operation::BUNDLE_PLUGIN: Operation::BUNDLE_CORE
        );

        if (isset($entity)) {
            $operation->setFromVersion($oldVersion);
        }

        $operation->setToVersion($version);
        $operation->setDependencies(array());
        $operationHandler->addOperation($operation, false);
    }

    public function checkInstallRequirements()
    {
        $rootDir = $this->kernelRootDir . '/..';

        $writableElements = array(
            'vendor' => 'directory',
            'web' => 'directory',
            'app/cache' => 'directory',
            'app/config/bundles.ini' => 'file',
            'vendor/composer/autoload_namespaces.php' => 'file'
        );

        $data = array();

        foreach ($writableElements as $el => $type) {
            $data[$el] = is_writable($rootDir . "/$el");
        }

        return $data;
    }

    public function getConfigurablePlugins()
    {
        return $this->pluginRepository->findBy(array('hasOptions' => true));
    }

    public function refresh($date)
    {
        $logFile = $this->getRefreshLog() . '-' . $date;
        $logFile .= '.log';
        $output = new StreamOutput(fopen($logFile, 'a', false));
        $this->refresher->setOutput($output);
        $this->refresher->installAssets();
        $this->refresher->dumpAssets('prod');
    }
}
