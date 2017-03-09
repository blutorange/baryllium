<html>
    <head>
        <title><?=$this->e($title)?></title>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="/resource/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="/resource/bootstrap/css/bootstrap-theme.min.css">
        <link rel="stylesheet" type="text/css" href="/resource/css/030-parsley.css">
        <link rel="stylesheet" type="text/css" href="/resource/css/060-master.css">
        <script type="text/javascript" src="/resource/js/000-jquery.js"></script>
        <script type="text/javascript" src="/resource/js/010-bootstrap.js"></script>
        <script type="text/javascript" src="/resource/js/020-parsley.js"></script>
        <script type="text/javascript" src="/resource/js/040-master.js"></script>
    </head>
    <body>
        <?=$this->section('content')?>
        <script type="text/javascript">
            $(document).ready(function () {
                $('[data-bootstrap-parsley]').parsley({
                    successClass: 'has-success',
                    errorClass: 'has-error',
                    classHandler: function(field) {
                        return field.$element.closest('.form-group');
                    },
                    errorsWrapper: '<ul class=\"help-block\"></ul>',
                    errorElem: '<li></li>'
                });
            });
        </script>
    </body>
</html>