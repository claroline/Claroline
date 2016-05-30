<?php

$maintenanceMode = file_exists(__DIR__.'/../app/config/.update');

if (!$maintenanceMode) {
    $url = $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'/../app.php';
    header("Location: http://{$url}");
}

class maintenance
{
    private $connection;

    public function __construct()
    {
        $config = $this->getConfig();

        try {
            $this->connection = new PDO($config['dsn'], $config['user'], $config['password']);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            $this->connection = false;
        }
    }

    public function getMaintenanceMessage()
    {
        if ($this->connection) {
            $sql = 'SELECT `content` FROM `claro_content` WHERE `type` = "claro_maintenance_message"';
            $q = $this->connection->query($sql);

            if ($q) {
                return $q->fetchColumn();
            }
        }

        return;
    }

    private function getConfig()
    {
        $dsn = '';
        $dbHost = '';
        $dbName = '';
        $user = '';
        $password = '';
        $configPath = __DIR__.'/../app/config/parameters.yml';
        $lines = str_getcsv(file_get_contents($configPath), PHP_EOL);

        foreach ($lines as $line) {
            if (preg_match('/^\s*database_host:\s*(\S+)$/', $line, $matches)) {
                $dbHost = $matches[1];
            } elseif (preg_match('/^\s*database_name:\s*(\S+)$/', $line, $matches)) {
                $dbName = $matches[1];
            } elseif (preg_match('/^\s*database_user:\s*(\S+)$/', $line, $matches)) {
                $user = $matches[1];
            } elseif (preg_match('/^\s*database_password:\s*(\S+)$/', $line, $matches)) {
                $password = $matches[1];
            }
        }

        if (!empty($dbHost) && !empty($dbName)) {
            $dsn = 'mysql:dbname='.$dbName.';host='.$dbHost;
        }

        return ['dsn' => $dsn, 'user' => $user, 'password' => $password];
    }
}

$maintenance = new Maintenance();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap-theme.min.css">
</head>
<body style="padding-top:40px; background-color:#F7F7F9">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-offset-4 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Maintenance</h3>
                </div>
                <div class="panel-body text-center">
                    <?php
                        echo $maintenance->getMaintenanceMessage();
                    ?>
                    <a class="btn" href="app.php"><span class="glyphicon glyphicon-refresh"></span></a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
