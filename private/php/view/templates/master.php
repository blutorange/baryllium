<?php
$locale = $locale ?? 'de';
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
        <link href="https://fonts.googleapis.com/css?family=Overpass" rel="stylesheet">  

        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/bootstrap/css/bootstrap.min.css')) ?>">
        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/bootstrap/css/bootstrap-theme.min.css')) ?>">
        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/include-css/030-parsley.css')) ?>">
        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/040-simplesidebar.css')) ?>">
        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/include-css/050-bootstrap-markdown.css')) ?>">
        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/include-css/060-dropzone.css')) ?>">
        <link rel="stylesheet" type="text/css" href="<?= $this->e($this->getResource('resource/css/090-master.css')) ?>">
        
        <style>
            @font-face {
                font-family: 'Overpass';
                font-style: normal;
                font-weight: 400;
                src: local('Overpass Regular'), local('Overpass-Regular'), url("<?= $this->e($this->getResource('resource/font/overpass-regular-latin-ext.woff2'))?>") format('woff2');
                unicode-range: U+0100-024F, U+1E00-1EFF, U+20A0-20AB, U+20AD-20CF, U+2C60-2C7F, U+A720-A7FF;
            }
            @font-face {
                font-family: 'Overpass';
                font-style: normal;
                font-weight: 400;
                src: local('Overpass Regular'), local('Overpass-Regular'), url("<?= $this->e($this->getResource('resource/font/overpass-regular-latin.woff2'))?>") format('woff2');
                unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02C6, U+02DA, U+02DC, U+2000-206F, U+2074, U+20AC, U+2212, U+2215;
            }
        </style>
        
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/000-prefixfree.js')) ?>"></script>    
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/001-jquery.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/002-jquery-loadingoverlay.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/002-jquery-jscroll.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/010-bootstrap.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/020-parsley.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource("resource/locale/020-parsley-$locale.js")) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/030-markdown.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/031-to-markdown.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/040-dropzone.js')) ?>"></script> 
        <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/040-dropzone-$locale.js")) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/050-bootstrap-markdown.js')) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource("resource/js/050-bootstrap-markdown-$locale.js")) ?>"></script>
        <script type="text/javascript" src="<?= $this->e($this->getResource('resource/js/090-master.js')) ?>"></script>

        <script type="text/javascript">
            (function ($, window, undefined) {
                var locale = "<?= $this->e($locale) ?>";
                var loadingGif = "<?= $this->e($this->getResource('resource/other/loading.gif')) ?>";
                var documentServlet = "<?= $this->e($this->getResource(Moose\Servlet\DocumentServlet::getRoutingPath())) ?>";
                var postServlet = "<?= $this->e($this->getResource(\Moose\Servlet\PostServlet::getRoutingPath())) ?>";
                window.moose = {
                    loadingGif: loadingGif,
                    locale: locale,
                    paths: {
                        'documentServlet': documentServlet,
                        'postServlet': postServlet
                    },
                    getElementValue: function ($element) {
                        var val;
                        if (($element).attr('type') === 'checkbox') {
                            val = $element.prop('checked');
                        } else {
                            val = $element.val();
                        }
                        return val;
                    },
                    setElementValue: function ($element, value) {
                        if (($element).attr('type') === 'checkbox') {
                            $element.prop('checked', Boolean(value));
                        } else {
                            $element.val(value);
                        }
                    },
                    getClientConfiguration: function(namespace, key, defaultValue) {
                        var json;
                        try {
                            json = $.parseJSON(localStorage[namespace]);
                        } catch (ignored) {
                            json = null;
                        }
                        if (!$.isPlainObject(json)) {
                            json = {};
                            window.localStorage[namespace] = JSON.stringify(json);
                        }
                        if (arguments.length === 1)
                            return json;
                        var stringKey = String(key);
                        return json.hasOwnProperty(stringKey) ? json[stringKey] : defaultValue;
                    },
                    setClientConfiguration: function(namespace, key, value) {
                        var json = window.moose.getClientConfiguration(namespace);
                        json[String(key)] = value;
                        window.localStorage[namespace] = JSON.stringify(json);
                    },
                    loadingOverlayOptions: {
                        color: "rgba(255, 255, 255, 0.8)",
                        custom: "",
                        fade: [100, 400],
                        fontawesome: "",
                        image: loadingGif,
                        imagePosition: "center center",
                        maxSize: "100px",
                        minSize: "20px",
                        resizeInterval: 50,
                        size: "50%",
                        zIndex: 9999
                    },
                    markdownEditing: false,
                    markdownEditorCommonOptions: {
                        language: locale,
                        dropZoneOptions: {
                            uploadMultiple: true,
                            paramName: 'documents',
                            method: 'POST',
                            maxFiles: 10,
                            addRemoveLinks: true,
                            maxFilesize: 2, // MB
                            thumbnailHeight: 32,
                            previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-image\"><img data-dz-thumbnail /></div>\n  <div class=\"dz-details\">\n    <div class=\"dz-size\"><span data-dz-size></span></div>\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n  </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n  <div class=\"dz-success-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Check</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>\n      </g>\n    </svg>\n  </div>\n  <div class=\"dz-error-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Error</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">\n          <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>\n        </g>\n      </g>\n    </svg>\n  </div>\n</div>",
                            acceptedFiles: 'image/*',
                            init: function() {
                                var markdown = $('.md-input', this.element).data('markdown');
                                var caretPos = 0;
                                this.on('drop', function(e) {
                                    caretPos = markdown.$textarea.prop('selectionStart');
                                });
                                this.on('successmultiple', function(file, response) {
                                    var data = typeof(response)==="string" ? $.parseJSON(response) : response;
                                    $.each(data, function(index, link){
                                        file[index].deleteUrl = link;
                                        var text = markdown.$textarea.val();
                                        markdown.$textarea.val(text.substring(0, caretPos) + '\n![description](' + link + ')\n' + text.substring(caretPos));    
                                    });
                                });
                                this.on('removedfile', function(file) {
                                    $.ajax(file.deleteUrl, {
                                        async: true,
                                        cache: false,
                                        method: 'DELETE',
                                        dataType: 'json'
                                    }).done(function (data, textStatus, jqXHR) {
                                        
                                    });
                                });
                                this.on('error', function(file, error, xhr) {
                                    if (!xhr) {
                                        alert(error);
                                        return;
                                    }
                                    try {
                                        var data = $.parseJSON(xhr.responseText);
                                        alert(data.error.message + ": " + data.error.details);
                                    }
                                    catch (e) {
                                        console.error(xhr.responseText, e);
                                        alert("Could not upload image. Please try again later.");
                                    }
                                });
                            }
                        },
                        //hiddenButtons: ['cmdImage'],
                        additionalButtons: [
                            [{
                                    name: "groupLink",
                                    data: [{
                                            name: "cmdCustomImage",
                                            toggle: false,
                                            title: "Insert image",
                                            icon: {
                                                glyph: 'glyphicon glyphicon-upload',
                                                fa: 'fa fa-picture-o',
                                                'fa-3': 'icon-picture',
                                                octicons: 'octicon octicon-file-media'
                                            },
                                            callback: function (editor) {
                                                editor.$editor.trigger('click');
                                            }
                                        }]
                                }]
                        ]
                    }
                };
                $.extend(window.moose.markdownEditorCommonOptions.dropZoneOptions, $.fn.dropzone.messages);
            })(jQuery, window, undefined);
        </script>
    </head>
    <body>
        <?= $this->section('content') ?>
    </body>
</html>
