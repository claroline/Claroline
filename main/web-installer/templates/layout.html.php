<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Claroline installer</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css">
    <link rel="shortcut icon" href="<?php echo $path('/../claroline.ico') ?>" />
    <script src="//code.jquery.com/jquery-git1.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>
    <style>
        body { background: #E2E2E2; }
        .navbar {
            height: 53px;
            background: #428BCA;
            border-bottom: 3px solid #f89406;
            -webkit-box-shadow: 0 2px 3px rgba(0 ,0, 0 , 0.25);
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.25);
        }
        .navbar img { height: 35px; margin: 7px auto; }
        .required { color: #777; font-weight: normal; }
    </style>
</head>
<body>
    <nav class="navbar navbar-static-top navbar-inverse" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <img src="<?php echo $path('/../uploads/logos/clarolineconnect.png') ?>"/>
            </div>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php echo $trans($var('stepTitle')) ?></h3>
                    </div>
                    <?php echo $render($var('stepTemplate'), $var('stepVariables')) ?>
                </div>

            </div>
        </div>
    </div>
    <script>
        $('.auto-submit').on('change', function () {
            this.form.submit();
        });
        $('#do-install').on('click', function (event) {
            $(this).addClass('disabled');
            $('#pre-install').addClass('disabled');
            var msg = '<?php echo $trans('please_wait') ?>',
                points = ['&nbsp;&nbsp;&nbsp;', '.&nbsp;&nbsp;', '..&nbsp;', '...'],
                index = 1,
                that = this;
            that.innerHTML = msg + points[0];
            setInterval(function () {
                that.innerHTML = msg + points[index];
                index = ++index > 3 ? 0 : index;
            }, 500);

            if ($('.sendData .radio input[name="sendData"]:checked').val()) {
                var postData = {
                    'ip': '<?php echo $getIp(); ?>',
                    'url': '<?php echo $getURL(); ?>',
                    'lang': '<?php echo $getLang(); ?>',
                    'country': '<?php echo $getCountry(); ?>',
                    'email': '<?php echo $getEmail(); ?>',
                    'version': '<?php echo $getVersion(); ?>',
                    'workspaces': 1,
                    'users': 1
                }

                $.ajax('http://stats.claroline.net/alive.php').done(function (data) {
                    if (data === 'true') {
                        $.post('http://stats.claroline.net/insert.php', postData);
                    }
                });
            }
        });
    </script>
</body>
</html>
