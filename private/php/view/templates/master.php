<?php
    use Moose\Servlet\DocumentServlet;
    use Moose\Servlet\PostServlet;
    $locale = $locale ?? 'de';
    $isDevMode = $isDevMode ?? false;
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?= $this->e($title) ?></title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="The MOOSE project">
        <meta name="author" content="The MOOSE team.">
        <meta name="theme-color" content="#539df0">

        <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->e($this->getResource('apple-touch-icon.png')) ?>">
        <link rel="icon" type="image/png" href="<?= $this->e($this->getResource('favicon-32x32.png')) ?>" sizes="32x32">
        <link rel="icon" type="image/png" href="<?= $this->e($this->getResource('favicon-16x16.png')) ?>" sizes="16x16">
        <link rel="manifest" href="<?= $this->e($this->getResource('manifest.json')) ?>">
        <link rel="mask-icon" href="<?= $this->e($this->getResource('safari-pinned-tab.svg')) ?>" color="#5bbad5">

        <style>
            @font-face {
                font-family: 'Overpass';
                font-style: normal;
                font-weight: 400;
                src: local('Overpass Regular'), local('Overpass-Regular'), url("<?= $this->e($this->getResource('resource/fonts/overpass-regular-latin-ext.woff2'))?>") format('woff2');
                unicode-range: U+0100-024F, U+1E00-1EFF, U+20A0-20AB, U+20AD-20CF, U+2C60-2C7F, U+A720-A7FF;
            }
            @font-face {
                font-family: 'Overpass';
                font-style: normal;
                font-weight: 400;
                src: local('Overpass Regular'), local('Overpass-Regular'), url("<?= $this->e($this->getResource('resource/fonts/overpass-regular-latin.woff2'))?>") format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2212, U+2215;
            }
        </style>

        <script type="text/javascript">
            (function (window, undefined) {
                window.Moose = {
                    Environment: {
                        locale: "<?= $this->e($locale) ?>",
                        loadingGif: "<?=$this->e($this->getResource('resource/other/loading.gif'))?>",
                        paths: {
                            'documentServlet': "<?= $this->e($this->getResource(DocumentServlet::getRoutingPath())) ?>",
                            'postServlet': "<?= $this->e($this->getResource(PostServlet::getRoutingPath())) ?>"
                        },
                        loadingOverlayOptions: {
                            color: "rgba(255, 255, 255, 0.8)",
                            custom: "",
                            fade: [100, 400],
                            fontawesome: "",
                            image: "<?=$this->e($this->getResource('resource/other/loading.gif'))?>",
                            imagePosition: "center center",
                            maxSize: "100px",
                            minSize: "20px",
                            resizeInterval: 50,
                            size: "50%",
                            zIndex: 9999
                        }
                    }
                };
            })(window);
        </script>
        
        <?php if ($isDevMode) : ?>
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/010-bootstrap.css')) ?>">
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/010-bootstrap-theme.css')) ?>">
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/030-parsley.css')) ?>">
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/less-css/050-bootstrap-markdown.css')) ?>">
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/060-dropzone.css')) ?>">
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/less-css/090-master.css')) ?>">
        
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/001-jquery.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/002-jquery-loadingoverlay.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/002-jquery-jscroll.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/002-jquery-hideshowpassword.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/010-bootstrap.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/020-parsley.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/021-parsley-$locale.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/030-markdown.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/031-to-markdown.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/040-dropzone.js')) ?>"></script> 
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/041-dropzone-$locale.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/050-bootstrap-markdown.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/060-js-cookie.js')) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/051-bootstrap-markdown-$locale.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/200-moose-util.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/201-moose-jqueryext.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/202-moose-persistence.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/203-moose-navigation.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/204-moose-forms.js")) ?>"></script>
            <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/205-moose-markdown.js")) ?>"></script>
        <?php else : ?>
            <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/build/css/all.prefix.min.css')) ?>">
            <script type="text/javascript" src="<?= $this->e($this->getResource('resource/build/js/all.min.js')) ?>"></script>
        <?php endif; ?>
            
            <script type="text/javascript">
                // For each module we call its onDocumentReady functon on document ready
                // if it exists. This provides a simple mechanism for each module to
                // initialize certain form elements etc.
                window.Moose.jQueryExtension.registerAll();
                $(window.document).ready(function () {
                    $.each(Moose, function(_, module) {
                        if (module.onDocumentReady && typeof module.onDocumentReady === 'function') {
                            module.onDocumentReady();
                        }
                    });
                });
            </script>
    </head>
    <body>
        <?= $this->section('content') ?>
    </body>
</html>
