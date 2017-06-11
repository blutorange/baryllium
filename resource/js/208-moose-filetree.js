/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Schedule = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;
    
    function setupFileTree(element) {
        $element = $(element);
        $element.empty().fancytree({
            checkbox: true,
            // Initial dataset
            source: [
                {
                    title: "Node 1",
                    key: "1",
                    data: {
                        customData: 42
                    },
                    lazy: true
                },
                {
                    title: "Folder 2",
                    key: "2",
                    folder: true,
                    children: [
                        {
                            title: "Node 2.1",
                            key: "3"
                        },
                        {
                            title: "Node 2.2",
                            key: "4"
                        }
                    ]
                }
            ],
            // Lazy loading
            lazyLoad: function(event, data) {
                var node = data.node;
                var dfd = new $.Deferred();
                data.result = dfd.promise();
                // TODO: Actual ajax request.
                console.log(node);
                var data = {
                    action: 'tree',
                    entity: {
                        fields: {
                            documentId: node.key,
                            depth: 1
                        }
                    }
                };
                window.setTimeout(function() {
                    dfd.resolve([
                        { title: "node 1", lazy: true },
                        { title: "node 2", select: true }
                    ]);
                }, 1500);
            },
            activate: function(event, data) {
                $('#ftd_filename').text(data.node.title)
                console.log(data.node);
            }
        });
    }
    
    function onDocumentReady() {
        $('.filetree-auto').eachValue(setupFileTree);
    }

    return {
        onDocumentReady: onDocumentReady,
    };        
};
