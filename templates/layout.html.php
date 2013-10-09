<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
    <script src="//code.jquery.com/jquery-git1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <style>
        .navbar, .navbar img { height: 50px; background: #111; }
        .navbar img { height: 40px; margin: 4px; }
        .required { color: #777; font-weight: normal; }
        .info-txt { margin: auto auto 20px; }
        .panel-body { max-width: 94%; margin: 24px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-static-top navbar-inverse" role="navigation">
        <div class="navbar-header">
            <img src="<?php echo $path('/../uploads/logos/clarolineconnect.png') ?>"/>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $trans($var('stepTitle')) ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php echo $render($var('stepTemplate'), $var('stepVariables')) ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script>
        $('.auto-submit').on('change', function () {
            this.form.submit();
        });
    </script>
</body>
</html>
