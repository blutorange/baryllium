/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Datatable = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;
    var df = Moose.Library.DateFormat;
    
    var renderers = {
        date: {
            display: function(logical) {
                if ($.isNumeric(logical)) {
                    var date = new Date(Number(logical));
                    if (date.getYear() === 70) date = new Date(Number(1000*logical));
                    return _.escape(df(date, Moose.Environment.dateFormat));
                }
                return _.escape(df(new Date(logical), Moose.Environment.dateFormat));
            },
            sort: function(logical) {
                return logical;
            }
        },
        studentid: {
            display: function(logical) {
                return logical ? _.escape("s" + logical) : '';
            },
            sort: function(logical) {
                return logical;
            }
        },
        badge: {
            display: function(logical) {
                return '<span class="badge">' + _.escape(logical) + '</span>';
            },
            sort: function(logical) {
                return logical;
            }
        },
        image: {
            display: function(logical) {
                return '<img class="cell-image" src="' + _.escape(logical)  + '">';
            },
            sort: function(logical) {
                return logical;
            }
        },
        text: {
            display: function(logical) {
                return _.escape(logical);
            },
            sort: function(logical) {
                return logical;
            }
        },
        html: {
            display: function(logical) {
                return logical;
            },
            sort: function(logical) {
                return logical;
            }
        }
    };

    /**
     * https://datatables.net/manual/server-side
     * @param {jQuery} $element
     * @returns {Function}
     */
    function getRequestProcessor($element) {
        return function(requestData, callback, setting){
            console.log("requestData", requestData);
            var url = $element.data('url');
            var search = {};
            $.eachValue(requestData.columns, function(column) {
                if (column.search.value.trim().length > 0)
                    search[column.name] = column.search.value;
            });
            var queryParams = {
                action: $element.data('action') || 'list',
                off: requestData.start,
                cnt: requestData.length,
                sdr: ((requestData.order||[{}])[0]||{}).dir||'asc',
                src: search
            };
            var sortCol = ((requestData.order||[{}])[0]||{}).column;
            if (sortCol !== undefined) {
                queryParams.srt = requestData.columns[sortCol].name;
            }
            console.log('queryParams', queryParams);
            var parser = document.createElement('a');
            parser.href = url;
            parser.search = (parser.search.length === 0 ? '?' : '&') + $.param(queryParams);
            Moose.Util.ajaxServlet(parser.href, 'GET', requestData, getResponseProcessor($element, requestData, callback));
        };
    }

    function getResponseProcessor($element, requestData, callback) {
        return function(responseData) {
            var configColumns = $element.data('columns');
            console.log("responseData",responseData);
            var rows = {
                draw: requestData.draw,
                recordsTotal: responseData.countTotal,
                recordsFiltered: responseData.countTotal,
                data: _.map(responseData.entity, function(entity) {
                    return _.map(requestData.columns, function(column){
                        return entity.fields[column.name];
                    });
                })
            };
            console.log("datatablesData", rows);
            callback(rows);
        };
    }

    function setupDatatableServerSide($element) {
        var config = $.extend($element.data(), {
            ajax: getRequestProcessor($element),
            serverSide: true,
            processing: true
        });
        console.log("config", config);
        return $element.DataTable(config);
    }
    
    function setupColumnSearch(table, config) {
        table.columns().every(function () {
            var that = this;
                            console.log(this)
            $('input.col-search', this.footer()).eachValue(function(footer){
                $(footer).on('change', _.debounce(function() {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                }, config.searchDelay));
            });
        });
    }

    function setupDatatable(element) {
        $element = $(element);
        var columns = $element.find('thead > tr > th').map(function(index) {
            var $column = $(this);
            var columnConfig = $.extend($column.data(), {
            });
            var renderer = renderers[columnConfig.type] || renderers.text;
            columnConfig.render = function(data, type, row, meta) {
                return renderer[type](data);
            }
            return columnConfig;
        });
        var config = $.extend($element.data(), {
            columns: columns,
            language: {
                url: window.Moose.Environment.paths.dataTableI18n
            }
        });
        config.order = config.orderInitial ? [[config.orderInitial, config.orderInitialDir || 'asc']] : [];
        var table = config.url ? setupDatatableServerSide($element) : null;
        setupColumnSearch(table, config);
    }

    function onDocumentReady() {
        $('.moose-datatable').eachValue(setupDatatable);
    }

    return {
        onDocumentReady: onDocumentReady
    };        
};