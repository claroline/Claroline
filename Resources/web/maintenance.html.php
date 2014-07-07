<?php

$maintenanceMode = file_exists(__DIR__ . '/../app/config/.update');

if (!$maintenanceMode) {
    $url = $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '/../app.php';
    header("Location: http://{$url}");
}

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
<div class="container">
    <div class="row">
        <div class="col-md-offset-4 col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Maintenance</h3>
                </div>
                <div class="panel-body text-center">
                    <p>Le site est temporairement en maintenance</p>
                    <p>The site is temporarily down for maintenance</p>
                    <a class="btn" href="app.php"><span class="glyphicon glyphicon-refresh"></span></a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
