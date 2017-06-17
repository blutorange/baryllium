/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Schedule = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;

    function mapperDocumentNode(node) {
        var fields = node.fields;
        var mapped = {
            key: fields.id,
            title: fields.documentTitle || fields.fileName,
            folder: fields.isDirectory,
            data: fields,
            lazy: true,
            expanded: false,
            selected: false
        };
        return mapped;
    };
    
    function setupFileTree(element) {
        $element = $(element);
        $element.empty().fancytree({
            checkbox: true,
            // Initial dataset
            source: [
                {
                    title: "Root",
                    key: 12,
                    lazy: true,
                    folder: true
                },
            ],
            // Lazy loading

            /*
             http://localhost:8082/public/servlet/document.php?action=tree&entity[fields][documentId]=12&entity[fields][depth]=10
{
	"success": true,
	"entity": [{
		"fields": {
			"fileName": "Dir_A",
			"documentTitle": "Directory A",
			"description": null,
			"isDirectory": true,
			"createTime": 1497723316,
			"children": [{
				"fields": {
					"fileName": "Dir_B",
					"documentTitle": "Directory B",
					"description": null,
					"isDirectory": true,
					"createTime": 1497723370,
					"children": [{
						"fields": {
							"fileName": "Dir_C1",
							"documentTitle": "Directory C1",
							"description": null,
							"isDirectory": true,
							"createTime": 1497723388,
							"children": []
						}
					}, {
						"fields": {
							"fileName": "Dir_C2",
							"documentTitle": "Directory C2",
							"description": null,
							"isDirectory": true,
							"createTime": 1497723394,
							"children": [{
								"fields": {
									"fileName": "Dir_D1",
									"documentTitle": "Directory D1",
									"description": null,
									"isDirectory": true,
									"createTime": 1497723426,
									"children": [{
										"fields": {
											"fileName": "bluebrain.jpg",
											"documentTitle": "bluebrain",
											"description": null,
											"isDirectory": false,
											"createTime": 1494278548,
											"children": []
										}
									}]
								}
							}, {
								"fields": {
									"fileName": "Dir_D2",
									"documentTitle": "Directory D2",
									"description": null,
									"isDirectory": true,
									"createTime": 1497723436,
									"children": []
								}
							}]
						}
					}, {
						"fields": {
							"fileName": "Dir_C3",
							"documentTitle": "Directory C3",
							"description": null,
							"isDirectory": true,
							"createTime": 1497723408,
							"children": []
						}
					}]
				}
			}, {
				"fields": {
					"fileName": "bluebrain.jpg",
					"documentTitle": "bluebrain",
					"description": null,
					"isDirectory": false,
					"createTime": 1494260200,
					"children": []
				}
			}, {
				"fields": {
					"fileName": "1494289443030-790677197.jpg",
					"documentTitle": "1494289443030-790677197",
					"description": null,
					"isDirectory": false,
					"createTime": 1494289460,
					"children": []
				}
			}, {
				"fields": {
					"fileName": "coding_hell.png",
					"documentTitle": "coding_hell",
					"description": null,
					"isDirectory": false,
					"createTime": 1494430671,
					"children": []
				}
			}]
		}
	}]
}
             */
            lazyLoad: function(event, data) {
                var node = data.node;
                var deferred = new $.Deferred();
                data.result = deferred.promise();
				if (node.folder === false) {
                    deferred.resolve([]);
                }
                var ajaxData = {
                    action: 'tree',
                    entity: {
                        fields: {
                            documentId: parseInt(node.key),
                            depth: 1,
                            includeParent: false
                        }
                    }
                };
                var onSuccess = function(json) {
                    var nodes = _.map(json.entity[0] || [], mapperDocumentNode);
                    deferred.resolve(nodes);
                };
                Moose.Util.ajaxServlet(Moose.Environment.paths.documentServlet,
                    'GET', ajaxData, onSuccess, true);
            },
            activate: function(event, data) {
                //TODO debug, remove me
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
