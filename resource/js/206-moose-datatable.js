/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

window.Moose.Factory.Datatable = function(window, Moose, undefined) {
    var $ = Moose.Library.jQuery;
    var _ = Moose.Library.Lodash;
    var m = Moose.Library.Moment;
    
    var handlers = {
      rowClick: {
          toogleChildColumn: function($row) {
              $($row.table().node()).data('responsiveInstance')._detailsDisplay($row, false);
          },
          gotoUserProfile: function($row) {
              var idColumn = _.findIndex($row.settings()[0].aoColumns, function(column){
                  return column.name === 'id';
              });
              if (idColumn >= 0 && idColumn < $row.data().length) {
                  var id = $row.data()[idColumn];
                  if (id !== null && id !== undefined) {
                      window.location.href = Moose.Environment.paths.profilePage + '?uid=' + id;
                  }
              }
          }
      }  
    };
    
    var renderers = {
        date: {
            display: function(logical) {
                if (logical === null) return _.escape('-');                
                if ($.isNumeric(logical)) {
                    // Check if we got seconds or milliseconds sicne 1970
                    var date = new Date(Number(logical));
                    if (date.getYear() === 70) date = new Date(Number(1000*logical));
                    return _.escape(m(date).format(Moose.Environment.dateFormat));
                }
                return _.escape(m(new Date(logical)).format(Moose.Environment.dateFormat));
            },
            sort: function(logical) {
                return logical;
            }
        },
        studentid: {
            display: function(logical) {
                if (logical === null) return _.escape('-');                
                return logical ? _.escape("s" + logical) : '';
            },
            sort: function(logical) {
                return "3840435";
            }
        },
        badge: {
            display: function(logical) {
                if (logical === null) return _.escape('-');                
                return '<span class="badge">' + _.escape(logical) + '</span>';
            },
            sort: function(logical) {
                return logical;
            }
        },
        image: {
            display: function(logical) {
                if (logical === null) return _.escape('-');
                return '<img class="cell-image" src="' + _.escape(logical)  + '">';
            },
            sort: function(logical) {
                return logical;
            }
        },
        text: {
            display: function(logical) {
                if (logical === null) return _.escape('-');
                return _.escape(logical);
            },
            sort: function(logical) {
                return logical;
            }
        },
        html: {
            display: function(logical) {
                if (logical === null) return _.escape('-');
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
            Moose.Util.ajaxServlet(url, 'GET', queryParams, getResponseProcessor($element, requestData, callback));
        };
    }

    function getResponseProcessor($element, requestData, callback) {
        return function(responseData) {
            console.log("responseData",responseData);
            var rows = {
                draw: requestData.draw,
                recordsTotal: responseData.countTotal,
                recordsFiltered: responseData.countFiltered !== null ? responseData.countFiltered : responseData.countTotal,
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
    
    function setupColumnSearch(api, config) {
        api.columns().every(function () {
            var $column = this;
            $('.col-search', $column.footer()).eachValue(function(footer){
                $(footer).on('keyup change', _.debounce(function() {
                    if ($column.search() !== this.value) {
                        $column.search(this.value).draw();
                    }
                }, config.searchDelay));
            });
        });
    }
    
    function setupRowClick($element) {
        var rowClickHandler = handlers.rowClick[$element.data('rowClick')];
        if (rowClickHandler) {
            $element.on('draw.dt', function(event, settings) {
                $element.dataTable().api().rows().every(function () {
                    var $row = this;
                    $($row.node()).on('click', function() {
                        rowClickHandler($row);
                    });
                });
            });
        }
    }

    function setupDatatable(element) {
        var $element = $(element);
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
            dom: 'lrtip',
            responsive: false,
            language: {
                url: window.Moose.Environment.paths.dataTableI18n
            }
        });
        config.order = config.orderInitial ? [[config.orderInitial, config.orderInitialDir || 'asc']] : [];
        $element.on('init.dt', function(event, settings) {
            var api = $(this).dataTable().api();
            setupColumnSearch(api, config);
            // Setup responsive table.
            var responsive = new $.fn.dataTable.Responsive($element, {
                details: {
                    display: $.fn.dataTable.Responsive.display.childRow,
                }
            });
            $element.data('responsiveInstance', responsive);
        });
        setupRowClick($element);        
        config.url ? setupDatatableServerSide($element) : null;
    }

    function onDocumentReady() {
        $('.moose-datatable').eachValue(setupDatatable);
    }

    return {
        onDocumentReady: onDocumentReady
    };        
};