<?php
    $locale = $locale ?? 'de';
?>
<!DOCTYPE html>
<html>
    <head>
        <title><?=$this->e($title)?></title>
        <meta charset="UTF-8">
        <link rel="apple-touch-icon" sizes="180x180" href="<?=$this->e($this->getResource('apple-touch-icon.png'))?>">
        <link rel="icon" type="image/png" href="<?=$this->e($this->getResource('favicon-32x32.png'))?>" sizes="32x32">
        <link rel="icon" type="image/png" href="<?=$this->e($this->getResource('favicon-16x16.png'))?>" sizes="16x16">
        <link rel="manifest" href="<?=$this->e($this->getResource('manifest.json'))?>">
        <link rel="mask-icon" href="<?=$this->e($this->getResource('safari-pinned-tab.svg'))?>" color="#5bbad5">
        
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="The MOOSE project">
        <meta name="author" content="The MOOSE team.">
        <meta name="theme-color" content="#539df0">
        
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/bootstrap/css/bootstrap.min.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/bootstrap/css/bootstrap-theme.min.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/include-css/030-parsley.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/css/040-simplesidebar.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/include-css/050-bootstrap-markdown.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/include-css/060-dropzone.css'))?>">
        <link rel="stylesheet" type="text/css" href="<?=$this->e($this->getResource('resource/css/090-master.css'))?>">
        
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/000-jquery.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/001-jquery-loadingoverlay.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/002-jquery-jscroll.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/010-bootstrap.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/020-parsley.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource("resource/locale/020-parsley-$locale.js"))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/030-markdown.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/031-to-markdown.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/040-dropzone.js'))?>"></script> 
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/050-bootstrap-markdown.js'))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource("resource/js/050-bootstrap-markdown-$locale.js"))?>"></script>
        <script type="text/javascript" src="<?=$this->e($this->getResource('resource/js/090-master.js'))?>"></script>
        
        <script type="text/javascript">
            (function($, window, undefined){
                window.moose = {
                    loadingGif: "<?=$this->e($this->getResource('resource/other/loading.gif'))?>",
                    locale: "<?=$this->e($locale)?>",
                    getElementValue: function($element) {
                        var val;
                        if (($element).attr('type')==='checkbox') {
                            val =  $element.prop('checked');
                        }
                        else {
                            val = $element.val();
                        }
                        return val;
                    },
                    setElementValue: function($element, value) {
                        if (($element).attr('type')==='checkbox') {
                            $element.prop('checked', Boolean(value));
                        }
                        else {
                            $element.val(value); 
                        }
                    },
                    getClientConfiguration(namespace, key, defaultValue) {
                        var json;
                        try {
                            json = $.parseJSON(localStorage[namespace]);
                        }
                        catch (ignored) {
                            json = null;
                        }
                        if (!$.isPlainObject(json)) {
                            json = {};
                            window.localStorage[namespace] = JSON.stringify(json);
                        }
                        if (arguments.length === 1) return json;
                        var stringKey = String(key);
                        return json.hasOwnProperty(stringKey) ? json[stringKey] : defaultValue;
                    },
                    setClientConfiguration(namespace, key, value) {
                        var json = window.moose.getClientConfiguration(namespace);
                        json[String(key)] = value;
                        window.localStorage[namespace] = JSON.stringify(json);
                    },
                    loadingOverlayOptions: {
                        color           : "rgba(255, 255, 255, 0.8)",
                        custom          : "",
                        fade            : [100,400],
                        fontawesome     : "",
                        image           : "<?=$this->e($this->getResource('resource/other/loading.gif'))?>",
                        imagePosition   : "center center",
                        maxSize         : "100px",
                        minSize         : "20px",
                        resizeInterval  : 50,
                        size            : "50%",
                        zIndex          : 9999
                    },
                    markdownEditing: false
                };
            })(jQuery, window, undefined);
        </script>
    </head>
    <body>
        <?=$this->section('content')?>
    </body>
</html>
