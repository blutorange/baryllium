/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Filetree = function(window, Moose, undefined) {
    "use strict";
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;
    var paths = Moose.Environment.paths;
    var ls = window.localStorage;
       
    var PREFIX_CACHE_OPAL = 'ftreeo.';
    var PREFIX_CACHE_INTERNAL = 'ftreei.';
    var KEY_ROOT = 'root';
    var KEY_OPAL = 'opal';
    var TYPE_INTERNAL = 'internal';
    var TYPE_OPAL = 'opal';
    var CACHE_LIFETIME_OPAL_DIR = 120*86400000; // 120*One day
    var CACHE_LIFETIME_OPAL_FILE = 7*86400000; // 7*One day
    var CACHE_LIFETIME_INTERNAL_NODE = 3600; // 1 hour

    var glyphOptions = {
        map: {
            doc: "glyphicon glyphicon-file",
            docOpen: "glyphicon glyphicon-file",
            checkbox: "glyphicon glyphicon-unchecked",
            checkboxSelected: "glyphicon glyphicon-check",
            checkboxUnknown: "glyphicon glyphicon-share",
            dragHelper: "glyphicon glyphicon-play",
            dropMarker: "glyphicon glyphicon-arrow-right",
            error: "glyphicon glyphicon-warning-sign",
            expanderClosed: "glyphicon glyphicon-menu-right",
            expanderLazy: "glyphicon glyphicon-menu-right",  // glyphicon-plus-sign
            expanderOpen: "glyphicon glyphicon-menu-down",  // glyphicon-collapse-down
            folder: "glyphicon glyphicon-folder-close",
            folderOpen: "glyphicon glyphicon-folder-open",
            loading: "glyphicon glyphicon-refresh glyphicon-spin"
        }
    };

    function converterFilesize(text, $element) {
        if (text < 1024)
            return String(text) + " Byte";
        if (text < 1024*1024)
            return (text/1024).toFixed(3) + " kB";
        if (text < 1024*1024*1024)
            return (text/(1024*1024)).toFixed(3) + " MB";
        return (text/(1024*1024*1024)).toFixed(3) + " GB";
    }

    function onNodeSelect(node, $base, $fancytree, $dropzone) {
        var data = node.data;
        var toHide;
        $base.find('.f-doc-id').data('id', data.id);
        switch (data.nodeType) {
            case TYPE_INTERNAL:
                onNodeSelectInternal(node, $base, $fancytree, $dropzone);
                toHide = '.f-opal';
                break;
            case TYPE_OPAL:
                onNodeSelectOpal(node, $base, $fancytree, $dropzone);
                toHide = '.f-internal';
                break;
            default:
                console.error('Unknown node type', data.nodeType);
                toHide = '.f-opal,.f-internal';
        }
        if (node.key === KEY_ROOT || node.key === KEY_OPAL) {
            $base.find('.f-root').show();
        }
        else {
            $base.find('.f-notroot').show();
        }   
        if (data.isDirectory) {
            $base.find('.f-dir').show();
            $base.find('.f-doc').hide();
        }
        else {
            $base.find('.f-dir').hide();
            $base.find('.f-doc').show();            
        }        
        if (node.key === KEY_ROOT || node.key === KEY_OPAL) {
            $base.find('.f-notroot').hide();
        }
        else {
            $base.find('.f-root').hide();
        }       
        $base.find(toHide).hide();
    }
    
    function onNodeSelectOpal(node, $base, $fancytree, $dropzone) {
        var data = node.data;
        opalNodeInfo($base, data);
        if (data.isDirectory) {
        }
        else {
            opalDownload(node, $base);
        }
    }

    function onNodeSelectInternal(node, $base, $fancytree, $dropzone) {
        var data = node.data;
        internalNodeInfo($base, data);
        updateDropzone(node, $dropzone);
        Moose.Navigation.setCallbackData($base.find('.btn-delete-dlg'), {
            id: data.id,
            fancytree: $fancytree.attr('id')
        });
        if (node.key === KEY_ROOT) {
            $base.find('.btn-delete-dlg').hide();
        }
        else if (node.parent.key === KEY_ROOT) {
            $base.find('.btn-delete-dlg').hide();
        }
        else if (data.isDirectory) {
            $base.find('.btn-delete-dlg').show();
        }
        else {
            internalPreviewAndDownload(node, $base);
            $base.find('.btn-delete-dlg').show();
        }
    }
    
    function updateDropzone(node, $dropzone) {
        var dropzoneOptions = $dropzone.data('instance').options;
        dropzoneOptions.url = dropzoneOptions.baseUrl + node.data.id;
    }
    
    function nodeInfo($base, data, fields) {
        $.each(fields, function(property, converter) {
            var $element = $($base[0].getElementsByClassName("f-" + property));
            var text = data[property];
            if ($element.length > 0) {
                $element.text(converter ? converter(text, $element) : text);
            }
        });
    }
        
    function opalNodeInfo($base, data) {
        nodeInfo($base, data, {
            fileName: function(text, $element) {
                return text || $element.data('emptytext') || '';
            },
            name: function(text, $element) {
                return text || $element.data('emptytext') || '';
            },
            description: function(text, $element) {
                return text || $element.data('emptytext') || '';
            },
            byteSize: converterFilesize,
            mimeType: null,
            modificationDate: function(text) {
                return Moose.Library.Moment.unix(window.parseInt(text)).format(Moose.Environment.dateTimeFormat);
            }
        });
    }
    
    function internalNodeInfo($base, data) {
        nodeInfo($base, data, {
            fileName: null,
            documentTitle: function(text, $element) {
                return text || $element.data('emptytext') || '';
            },
            description: function(text, $element) {
                return text || $element.data('emptytext') || '';
            },
            size: converterFilesize,
            mime: null,
            createTime: function(text) {
                return Moose.Library.Moment.unix(window.parseInt(text)).format(Moose.Environment.dateTimeFormat);
            }
        });
    }
    
    function opalDownload(node, $base) {
        var data = node.data;
        var $btnDownload = $base.find('.btn-download-opal').closest('a');
        var downloadUrl = paths.opalServlet + '?action=file&entity[fields][nodeId]=' + window.encodeURIComponent(data.id);
        $btnDownload.attr('href', downloadUrl);
        $btnDownload.attr('download', data.name);
        $btnDownload.attr('type', data.mimeType);
    }
    
    function internalPreviewAndDownload(node, $base) {
        var data = node.data;
        var $previewA = $base.find('.f-preview');
        var $btnDownload = $base.find('.btn-download-document').closest('a');
        var $previewImg = $previewA.children('img');
        var previewUrl = paths.documentServlet + '?action=single&tmb=true&did=' + data.id;
        var downloadUrl = paths.documentServlet + '?action=single&did=' + data.id;
        var isImage = String(data.mime).startsWith('image/');
        $btnDownload.attr('href', downloadUrl);
        $btnDownload.attr('download', data.fileName);
        $btnDownload.attr('type', data.mime);
        $previewA.attr('href', downloadUrl);
        $previewA.attr('type', data.mime);
        $previewImg.attr('alt', data.documentTitle);
        $previewImg.attr('title', data.documentTitle);
        $previewImg.attr('src', isImage ? downloadUrl : previewUrl);
    }

    function mapperDocumentNode(node) {
        var fields = node.fields;
        fields.nodeType = TYPE_INTERNAL;
        var mapped = {
            key: fields.id,
            title: fields.documentTitle || fields.fileName,
            folder: fields.isDirectory,
            data: fields,
            lazy: fields.isDirectory && fields.childCount > 0 && fields.children.length === 0 ? true : false,
            expanded: false,
            selected: false
        };
        if (fields.children.length > 0) {
            mapped.children = _.sortBy(_.map(node.fields.children, mapperDocumentNode), function(field) {
               return field.title;
            });
        }
        return mapped;
    }
    
    function mapperOpalNode(node) {
        var fields = node.fields;
        fields.nodeType = TYPE_OPAL;
        var mapped = {
            key: fields.id,
            title: fields.name,
            folder: fields.isDirectory,
            data: fields,
            lazy: fields.isDirectory,
            expanded: false,
            selected: false
        };
        return mapped;        
    }
    
    function lazyLoadNode(event, data, isRestore) {
        var node = data.node;
        var deferred = new $.Deferred();
        data.result = deferred.promise();
        if (node.folder === false) {
            deferred.resolve([]);
            return;
        }
        switch (node.data.nodeType) {
            case TYPE_INTERNAL:
                lazyLoadInternal(node, isRestore, deferred);
                break;
            case TYPE_OPAL:
                lazyLoadOpal(node, isRestore, deferred);
                break;
            default:
                console.error('Unknown node type', node.data.nodeType);
        }
    }
    
    function fetchCachedNode(node, prefix) {
        var cacheKey = prefix + node.key;
        var cached = ls[cacheKey];
        if (!cached)
            return null;
        try {
            var data = JSON.parse(cached);
        }
        catch (e) {
            console.error('Failed to read cached node data', e);
            return null;
        }
        if (!$.isPlainObject(data))
            return null;
        if (new Date().getTime() >= data.expire) {
            delete ls[cacheKey];
            return false;
        }
        return data.cached;
    }
    
    function storeCachedNode(node, data, prefix, cacheTime) {
        var cacheKey = prefix + node.key;
        ls[cacheKey] = JSON.stringify({
            expire: new Date().getTime() + cacheTime,
            cached: data
        });
    }
    
    function lazyLoadOpal(node, isRestore, deferred) {
        var cached = fetchCachedNode(node, PREFIX_CACHE_OPAL);
        if (cached === false && isRestore) {
            deferred.resolve([]);
            isRestore.push(node);
            return;
        }
        if (cached) return deferred.resolve(cached);
        var isRootNode = node.key === KEY_OPAL;
        var ajaxData = {
            action: 'node',
            entity: {
                fields: {
                    nodeId: isRootNode ? null : node.key,
                }
            }
        };
//        if (isRestore) {
//            ajaxData.entity.fields.expand = String(window.localStorage['fancytree-1-expanded']).split('~').join(',');
//        }
        var onSuccess = function(json) {
            var nodes = _.map(json.entity || [], mapperOpalNode);
            var hasFile = _.findIndex(nodes, function(node) {
                return !node.data.isDirectory;
            }) >= 0;
            var cacheTime = node.length === 0 || hasFile ? CACHE_LIFETIME_OPAL_FILE : CACHE_LIFETIME_OPAL_DIR;
            storeCachedNode(node, nodes, PREFIX_CACHE_OPAL, cacheTime);
            console.log(nodes);
            deferred.resolve(nodes);
        };
        var onFailure = function(error) {
            deferred.reject({
                message: error.message
            }
        )};
        Moose.Util.ajaxServlet({
            url: paths.opalServlet,
            method: 'GET',
            data: ajaxData,
            onSuccess: onSuccess,
            onFailure: onFailure
        });
    }
    
    function lazyLoadInternal(node, isRestore, deferred) {
        var isRootNode = node.key === KEY_ROOT;
        var ajaxData = {
            action: 'tree',
            entity: {
                fields: {
                    documentId: isRootNode ? null : parseInt(node.key),
                    depth: isRootNode ? 0 : 1,
                    includeParent: isRootNode,
                }
            }
        };
        if (isRestore) {
            ajaxData.entity.fields.expand = String(ls['fancytree-1-expanded']).split('~').join(',');
        }
        var onSuccess = function(json) {
            var nodes = _.map(json.entity[0] || [], mapperDocumentNode);
            deferred.resolve(nodes);
        };
        var onFailure = function(error) {
            deferred.reject({
                message: error.message
            });
        };
        Moose.Util.ajaxServlet({
            url: paths.documentServlet,
            method: 'GET',
            data: ajaxData,
            onSuccess: onSuccess,
            onFailure: onFailure
        });
    }
    
    function setupFileTree($base, $fancytree, $dropzone) {
        var isRestore = false;
        var extensions = ['glyph', 'wide', 'filter'];
        if (Moose.Persistence.getClientField('option.documents.treestore'))
            extensions.push('persist');
        var hasOpal = !!$base.data('hasOpal');
        var source = [
            {
                title: $fancytree.data('rootTitle'),
                key: KEY_ROOT,
                lazy: true,
                folder: true,
                data: {
                    nodeType: TYPE_INTERNAL,
                    isDirectory: true
                }
            }
        ];
        if (hasOpal) {
            source.push({
                title: $fancytree.data('opalTitle'),
                key: KEY_OPAL,
                lazy: true,
                folder: true,
                data: {
                    nodeType: TYPE_OPAL,
                    isDirectory: true
                }
            });
        }
        $fancytree.empty().fancytree({
            extensions: extensions,
            checkbox: true,
            // Initial dataset
            source: source,
            // Glyph options.
            glyph: glyphOptions,
            // Persistance options
            persist: {
                expandLazy: true,
                store: 'local',
                types: 'active expanded focus',
            },
            // Filter options
            quicksearch: true,
            filter: {
                autoApply: true,   // Re-apply last filter if lazy data is loaded
                autoExpand: false, // Expand all branches that contain matches while filtered
                counter: true,     // Show a badge with number of matching child nodes near parent icons
                fuzzy: false,      // Match single characters in order, e.g. 'fb' will match 'FooBar'
                hideExpandedCounter: true,  // Hide counter badge if parent is expanded
                hideExpanders: false,       // Hide expanders if all child nodes are hidden by filter
                highlight: true,   // Highlight matches by wrapping inside <mark> tags
                leavesOnly: false, // Match end nodes only
                nodata: true,      // Display a 'no data' status node if result is empty
                mode: "dimm"       // Grayout unmatched nodes (pass "hide" to remove unmatched node instead)
            },            
            // Lazy loading
            // http://localhost:8082/public/servlet/document.php?action=tree&entity[fields][documentId]=12&entity[fields][depth]=10
            lazyLoad: function(event, data){
                lazyLoadNode(event, data, isRestore);
            },
            // Events
            activate: function(event, data) {
                onNodeSelect(data.node, $base, $fancytree, $dropzone);
            },
            beforeRestore: function() {
                isRestore = [];
                return true;
            },
            restore: function() {
                if (isRestore) {
                    $.each(isRestore, function(_, node) {
                        node.resetLazy();
                    });
                }
                isRestore = false;
            }            
        });
    }
    
    function setupDropZone($base, $fancytree, $dropzone) {
        var dropZoneOptions = {
            url: $dropzone.data('action'),
            parallelUploads: 3,
            uploadMultiple: true,
            maxFilesize: '25',
            paramName: 'documents',
            previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  <div class=\"dz-image\"><img data-dz-thumbnail /></div>\n  <div class=\"dz-details\">\n    <div class=\"dz-size\"><span data-dz-size></span></div>\n    <div class=\"dz-filename\"><span data-dz-name></span></div>\n  </div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n  <div class=\"dz-success-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Check</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>\n      </g>\n    </svg>\n  </div>\n  <div class=\"dz-error-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Error</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">\n          <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>\n        </g>\n      </g>\n    </svg>\n  </div>\n</div>",
            //clickable: '.filetree-upload-button',
            success: function() {
                // Reload directory and show newly created entries.
                var node = $fancytree.fancytree('instance').tree.getActiveNode();
                if (node) {
                    node.resetLazy();
                    node.load().done(function() {
                        node.setExpanded();
                    });
                }
            }
        };
        var dropzone = new Dropzone($dropzone[0], $.extend(dropZoneOptions, $.fn.dropzone.messages));
        dropzone.options.baseUrl = $dropzone.attr('action');
        $dropzone.closest('.dropzone-container').addClass('dropzone');
        $dropzone.data('instance', dropzone);
    }
    
    function setupFileManager(base) {
        var $base = $(base);
        var $fancytree = $('.filetree-hierarchy', base);
        var $dropzone = $('.filetree-dropzone', $base);
        setupDropZone($base, $fancytree, $dropzone);
        setupFileTree($base, $fancytree, $dropzone);
        $base.show(200);
    }
    
    function onDocumentReady() {
        $('.file-manager').eachValue(setupFileManager);
    }

    return {
        onDocumentReady: onDocumentReady,
    };        
};
