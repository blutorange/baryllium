<html>
    <head>
        <title><?=$this->e($title)?></title>
        <meta charset="UTF-8">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" href="/favicon-32x32.png" sizes="32x32">
        <link rel="icon" type="image/png" href="/favicon-16x16.png" sizes="16x16">
        <link rel="manifest" href="/manifest.json">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="theme-color" content="#c46e48">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('/resource/bootstrap/css/bootstrap.min.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('/resource/bootstrap/css/bootstrap-theme.min.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('/resource/css/030-parsley.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('/resource/css/060-master.css'))?>">
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/000-jquery.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('/resource/js/010-bootstrap.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('/resource/js/020-parsley.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('/resource/locale/020-parsley-de.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('/resource/js/040-master.js'))?>"></script>
    </head>
    <body>
        <?=$this->section('content')?>
        <script type="text/javascript">
            $(document).ready(function () {
                window.parsley.setLocale('<?=$this->e($locale ?? 'de')?>');
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