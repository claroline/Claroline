<?php

include __DIR__ . '/authorize.php';

$ds = DIRECTORY_SEPARATOR;
$vendorDir = __DIR__ . "/../../vendor";
$configDir = realpath($vendorDir . "/../app/config");
$logId = $_GET['logId'];
$logFile = $vendorDir . '/../app/logs/pre_update-' . $logId . '.log';

require $vendorDir . '/autoload.php';

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
//check if the bundle table exists

$query = "SHOW TABLES LIKE 'claro_bundle'";
$res = $conn->query($query);

//create the table if it doesn't
$sql = "CREATE TABLE claro_bundle (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	name    VARCHAR(100),
	version VARCHAR(50)
)";

$logLine = $conn->query($sql) ?
	"Creating table claro_bundle...\n":
	"Table claro_bundle already exists.\n";
touch($logFile);
file_put_contents($logFile, $logLine, FILE_APPEND);

//update the table
$ds = DIRECTORY_SEPARATOR;
$jsonFile = "{$vendorDir}/composer/installed.json";
$data = json_decode(file_get_contents($jsonFile));

foreach ($data as $row) {
	if ($row->type === 'claroline-plugin' || $row->type === 'claroline-core') {
		$name = $row->name;
		$version = $row->version;
		//let's find if there is something in the database !
		$sql = "SELECT * from `claro_bundle` where `name` LIKE \"{$name}\"";
		$res = $conn->query($sql);

		if (count($res->fetchAll()) > 0) {
			$sql = "UPDATE `claro_bundle` set version='{$version}'
				where `name` LIKE \"{$name}\"";
			$conn->query($sql);
			$logLine = "Updating {$name} to the current version ({$version})\n";
		} else {
			//insert
			$sql = "INSERT into claro_bundle (name, version)
				VALUES('{$name}', '{$version}')";
			$conn->query($sql);
			$logLine = "Inserting {$name} to the current version ({$version})\n";
		}

		file_put_contents($logFile, $logLine, FILE_APPEND);
	}
}

$logLine = "Done !\n";
file_put_contents($logFile, $logLine, FILE_APPEND);
