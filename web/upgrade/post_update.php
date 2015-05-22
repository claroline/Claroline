<?php

ini_set("max_execution_time", 3600);
//ini_set("memory_limit", "512M");

include __DIR__ . '/authorize.php';

$vendorDir = __DIR__ . "/../../vendor";
$configDir = realpath($vendorDir . '/../app/config');
$logId = $_GET['logId'];
$logFile = $vendorDir . '/../app/logs/post_update-' . $logId . '.log';

require_once __DIR__ . '/../../app/bootstrap.php.cache';
include __DIR__ . '/libs.php';

use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Recorder;

use Psr\Log\LogLevel;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\HttpFoundation\Request;
use Claroline\CoreBundle\Library\Installation\Refresher;

require_once __DIR__. "/../../app/AppKernel.php";

$logLine = "Emptying the cache...\n";
file_put_contents($logFile, $logLine . "\n", FILE_APPEND);
Refresher::removeContentFrom($vendorDir . '/../app/cache');

//we can also get the PDO connection from the sf2 container.
//database parameters from the parameters.yml file
$value = Yaml::parse($configDir . '/parameters.yml');
$host = $value['parameters']['database_host'];
$dbName = $value['parameters']['database_name'];
//dsn driver... hardcoded. Change this if you really need it.
//or use the connexion from sf2
$driver = 'mysql';
$dsn = "{$driver}:host={$host};dbname={$dbName}";
$username = $value['parameters']['database_user'];
$password = $value['parameters']['database_password'];;

//create connection
$conn = new \PDO($dsn, $username, $password, array());

//Let's use stefk stuff !
$operationFilePath = __DIR__ . "/../../app/config/operations.xml";
//remove the old operation file if it exists (maybe it would be better to do a backup).
unlink($operationFilePath);
$operationHandler = new OperationHandler($operationFilePath);
$detector = new Detector($vendorDir);
$bundleHandler = new BundleHandler($configDir . '/bundles.ini');
$recorder = new Recorder(
    new Detector($vendorDir),
    new BundleHandler($configDir . '/bundles.ini'),
    new OperationHandler($configDir . '/operations.xml'),
    $vendorDir
);

$recorder->buildBundleFile();

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//I need to do that in order to access some services required for the installation...
$kernel->boot();

$bundles = array();

//retrieve the current bundles
foreach ($recorder->getOperations() as $operation) {
    $package = $operation->getPackage();
    if ($package->getType() === 'claroline-plugin' ||
        $package->getType() === 'claroline-core') {
        $bundles[] = array(
            'type'         => $package->getType(),
            'name'         => $package->getPrettyName(),
            'new_version'  => $package->getVersion(),
            'is_installed' => false,
            'fqcn'         => $detector->detectBundle($package->getPrettyName()),
            'dependencies' => array($recorder->getDependencies($package))
        );
    }
}

//retrieve the already installed bundles
$sql = "SELECT * from `claro_bundle`";
$res = $conn->query($sql);

$operations = [];

foreach ($res->fetchAll() as $installedBundle) {
    foreach ($bundles as &$bundle) {
        if ($bundle['name'] === $installedBundle['name']) {
            $bundle['is_installed'] = true;
            $bundle['old_version'] = $installedBundle['version'];
        }
    }
    //removing last ref
    unset($bundle);
}

//generating the operations.xml file
foreach ($bundles as $bundle) {
    $operation = new Operation(
        $bundle['is_installed'] ? Operation::UPDATE: Operation::INSTALL,
        $bundle['fqcn'],
        $bundle['type'] === 'claroline-plugin' ? Operation::BUNDLE_PLUGIN: Operation::BUNDLE_CORE
    );

    if (isset($bundle['old_version'])) {
        $operation->setFromVersion($bundle['old_version']);
    }

    $operation->setToVersion($bundle['new_version']);
    $operation->setDependencies($bundle['dependencies']);
    $operationHandler->addOperation($operation, false);
}

//Build the bundle file
$recorder->buildBundleFile();

//reboot the kernel for the new bundle file
$kernel->shutdown();
$kernel->boot();

//install from the operation file
$container = $kernel->getContainer();
    /** @var \Claroline\CoreBundle\Library\Installation\PlatformInstaller $installer */
$installer = $container->get('claroline.installation.platform_installer');

//assets & assetic dump
$installer->installFromOperationFile();
    /** @var \Claroline\CoreBundle\Library\Installation\Refresher $refresher */
$refresher = $container->get('claroline.installation.refresher');
$output = new StreamOutput(fopen($logFile, 'a', false));
$verbosityLevelMap = array(
    LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
    LogLevel::INFO   => OutputInterface::VERBOSITY_NORMAL,
    LogLevel::DEBUG  => OutputInterface::VERBOSITY_NORMAL
);
$consoleLogger = new ConsoleLogger($output, $verbosityLevelMap);
$installer->setLogger($consoleLogger);

$refresher->setOutput($output);
$refresher->installAssets();
$refresher->dumpAssets($container->getParameter('kernel.environment'));
$refresher->compileGeneratedThemes();
$installer->setBundleVersion();

$logLine = "Done\n";
file_put_contents($logFile, $logLine, FILE_APPEND);

exit(0);
