/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Schedule = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;
    
    var KEY_ALL_COURSES = 'root';

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

    function mapperDocumentNode(node) {
        var fields = node.fields;
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
            mapped.children = _.map(node.fields.children, mapperDocumentNode);
        }
        return mapped;
    };
    
    function lazyLoadNode(event, data, isRestore) {
        var node = data.node;
        var deferred = new $.Deferred();
        data.result = deferred.promise();
        if (node.folder === false) {
            deferred.resolve([]);
            return;
        }
        var allCourses = node.key === KEY_ALL_COURSES;
        var ajaxData = {
            action: 'tree',
            entity: {
                fields: {
                    documentId: allCourses ? null : parseInt(node.key),
                    depth: allCourses ? 0 : 1,
                    includeParent: allCourses,
                }
            }
        };
        if (isRestore) {
            ajaxData.entity.fields.expand = String(window.localStorage['fancytree-1-expanded']).split('~').join(',');
        }
        var onSuccess = function(json) {
            var nodes = _.map(json.entity[0] || [], mapperDocumentNode);
            deferred.resolve(nodes);
        };
        Moose.Util.ajaxServlet(Moose.Environment.paths.documentServlet, 'GET',
                ajaxData, onSuccess, true);
    }
    
    function setupFileTree(element) {
        $element = $(element);
        var isRestore = false;
        var extensions = ['glyph', 'wide', 'filter'];
        if (Moose.Persistence.getClientField('option.documents.treestore'))
            extensions.push('persist');
        $element.empty().fancytree({
            extensions: extensions,
            checkbox: true,
            // Initial dataset
            source: [
                {
                    title: $element.data('rootTitle'),
                    key: KEY_ALL_COURSES,
                    lazy: true,
                    folder: true
                }
            ],
            // Glyph options.
            glyph: glyphOptions,
            // Persistance options
            persist: {
                expandLazy: true,
                store: 'local',
                types: 'active expanded focus'
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
                //TODO debug, remove me
                $('#ftd_filename').text(data.node.data.fileName)
                console.log(data.node);
            },
            beforeRestore: function() {
                isRestore = true;
                return true;
            },
            restore: function() {
                isRestore = false;
            }            
        });
        //$element.fancytree('instance').getRootNode().getFirstChild().setExpanded(true);
    }
    
    function onDocumentReady() {
        $('.filetree-auto').eachValue(setupFileTree);
    }

    return {
        onDocumentReady: onDocumentReady,
    };        
};
