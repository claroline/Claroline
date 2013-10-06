<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
    <script src="//code.jquery.com/jquery-git1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-static-top navbar-inverse" role="navigation">
        <div class="navbar-header">
            <img src="<?php echo $path('/../test-logo-2.png') ?>"/>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <?php echo $render($var('stepTemplate'), $var('stepVariables')) ?>
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
