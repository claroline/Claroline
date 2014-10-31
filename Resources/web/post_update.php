<?php

include __DIR__ . '/authorize.php';

$ds = DIRECTORY_SEPARATOR;
$vendorDir = __DIR__ . "{$ds}..{$ds}vendor";
$configDir = realpath($vendorDir . "{$ds}..{$ds}app{$ds}config");

//I'm going to need some stefk libs...
require __DIR__ . "{$ds}..{$ds}vendor{$ds}autoload.php";

use Claroline\BundleRecorder\Operation;
use Claroline\BundleRecorder\Handler\OperationHandler;
use Claroline\BundleRecorder\Detector\Detector;
use Claroline\BundleRecorder\Handler\BundleHandler;
use Claroline\BundleRecorder\Recorder;
use Symfony\Component\Yaml\Yaml;

//database parameters from the parameters.yml file
$value = Yaml::parse($configDir . $ds . 'parameters.yml');
$host = $value['parameters']['database_host'];
$dbName = $value['parameters']['database_name'];
//dsn driver... hardcoded. Change this if you really need it.
$driver = 'mysql';
$dsn = "{$driver}:host={$host};dbname={$dbName}";
$username = $value['parameters']['database_user'];
$password = $value['parameters']['database_password'];;

//create connection
$conn = new \PDO($dsn, $username, $password, array());

//Let's use stefk stuff !
$operationFilePath = __DIR__ . "{$ds}..{$ds}app{$ds}config{$ds}operations.xml";
//remove the old operation file if it exists (maybe it would be better to do a backup).
unlink($operationFilePath);
$operationHandler = new OperationHandler($operationFilePath);
$detector = new Detector($vendorDir);
$bundleHandler = new BundleHandler($configDir . $ds . 'bundles.ini');

//parsing installed.json...
$bundles = array();
$ds = DIRECTORY_SEPARATOR;
$jsonFile = __DIR__ . "{$ds}..{$ds}vendor{$ds}composer{$ds}installed.json";
$data = json_decode(file_get_contents($jsonFile));

//retrieve the current bundles
foreach ($data as $row) {
    if ($row->type === 'claroline-plugin' || $row->type === 'claroline-core') {
        $bundles[] = array(
            'type'         => $row->type,
            'name'         => $row->name,
            'new_version'  => $row->version,
            'is_installed' => false,
            'fqcn'         => $detector->detectBundle($row->name)
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
    $operationHandler->addOperation($operation);
}

$fqcns = [];

foreach ($bundles as $bundle) {
    $fqcns[] = $bundle['fqcn'];
}

//Build the bundle file
$recorder = new Recorder(
	new Detector($vendorDir),
	new BundleHandler($configDir . '/bundles.ini'),
	new OperationHandler($configDir . '/operations.xml'),
	$vendorDir
);
$recorder->buildBundleFile();

//update prepare the next update
include __DIR__ . '/prepare_update.php';

//we must clear the cache
exec ("php " . __DIR__ . "{$ds}..{$ds}app{$ds}console cache:clear");
//now we can run the command claroline:update...
//We also can call these commandands programmatically
//(just check how it works in the claroline command folder ~but I'm lazy)
exec ("php " . __DIR__ . "{$ds}..{$ds}app{$ds}console claroline:update");
exec ("php " . __DIR__ . "{$ds}..{$ds}app{$ds}console assets:install");
exec ("php " . __DIR__ . "{$ds}..{$ds}app{$ds}console assetic:dump");
//now we can remove the operations.xml file
echo "Done !";
