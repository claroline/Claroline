<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .button {
            border: solid #999 1px;
            color: #000;
            background-color: #EEE;
            text-decoration: none;
        }
        .disabled {
            color: #AAA;
        }
    </style>

</head>
<body>
    <h1>Claroline</h1>

    <?php echo $render($var('stepTemplate'), $var('stepVariables')) ?>

    <script>
        for (var i = 0, disabled = document.getElementsByClassName('disabled'); i < disabled.length; ++i) {
            disabled[i].setAttribute('onclick', 'return false');
        }
    </script>
</body>
</html>