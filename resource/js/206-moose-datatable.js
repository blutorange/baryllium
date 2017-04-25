/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){   
    Moose.Datatable  = (function() {
        
        /**
         * https://datatables.net/manual/server-side
         * The data object may contain the following entries:
         * <ul>
         *   <li>draw</li> Must be parsed as integer and then echoed back.
         *   <li>start</li> Paging first record indicator. This is the start point in the current data set (0 index based - i.e. 0 is the first record).
         *   <li>length</li> Number of records that the table can display in the current draw. May be -1 to indicate that all records should be returned.
         *   <li>search[value]</li> Global search value. To be applied to all columns which have searchable as true.
         *   <li>search[regex]</li> true if the global filter should be treated as a regular expression for advanced searching, false otherwise.
         *   <li>order[i][column]</li>Column to which ordering should be applied.
         *   <li>order[i][dir]</li>Ordering direction for this column. Either asc or desc.
         *   <li>columns[i][data]</li>Column's data source.
         *   <li>columns[i][name]</li>Column's name.
         *   <li>columns[i][searchable]</li>Flag to indicate if this column is searchable or not.
         *   <li>columns[i][orderable]</li>Flag to indicate if this column is orderable or not.
         *   <li>columns[i][search][value]</li>Search value to apply to this specific column.
         *   <li>columns[i][search][regex]</li>Flag to indicate if the search term for this column should be treated as regular expression or not.
         * </ul>
         * @param object data Data to be sent to the server.
         * @param function callback Callback that should be called with the retrieved data.
         * @param object setting DataTables settings object.
         * @returns undefined
         */
        /* Sample request:
            draw:2
            columns[0][data]:0
            columns[0][name]:
            columns[0][searchable]:true
            columns[0][orderable]:true
            columns[0][search][value]:
            columns[0][search][regex]:false
            columns[1][data]:1
            columns[1][name]:
            columns[1][searchable]:true
            columns[1][orderable]:true
            columns[1][search][value]:
            columns[1][search][regex]:false
            columns[2][data]:2
            columns[2][name]:
            columns[2][searchable]:true
            columns[2][orderable]:true
            columns[2][search][value]:
            columns[2][search][regex]:false
            columns[3][data]:3
            columns[3][name]:
            columns[3][searchable]:true
            columns[3][orderable]:true
            columns[3][search][value]:
            columns[3][search][regex]:false
            columns[4][data]:4
            columns[4][name]:
            columns[4][searchable]:true
            columns[4][orderable]:true
            columns[4][search][value]:
            columns[4][search][regex]:false
            columns[5][data]:5
            columns[5][name]:
            columns[5][searchable]:true
            columns[5][orderable]:true
            columns[5][search][value]:
            columns[5][search][regex]:false
            order[0][column]:0
            order[0][dir]:asc
            start:20
            length:10
            search[value]:
            search[regex]:false
            _:1493148948700
         */
        /* Sample response
            {
                    "draw": 2,
                    "recordsTotal": 57,
                    "recordsFiltered": 57,
                    "data": [
                            ["Gloria", "Little", "Systems Administrator", "New York", "10th Apr 09", "$237,500"],
                            ["Haley", "Kennedy", "Senior Marketing Designer", "London", "18th Dec 12", "$313,500"],
                            ["Hermione", "Butler", "Regional Director", "London", "21st Mar 11", "$356,250"],
                            ["Herrod", "Chandler", "Sales Assistant", "San Francisco", "6th Aug 12", "$137,500"],
                            ["Hope", "Fuentes", "Secretary", "San Francisco", "12th Feb 10", "$109,850"],
                            ["Howard", "Hatfield", "Office Manager", "San Francisco", "16th Dec 08", "$164,500"],
                            ["Jackson", "Bradshaw", "Director", "New York", "26th Sep 08", "$645,750"],
                            ["Jena", "Gaines", "Office Manager", "London", "19th Dec 08", "$90,560"],
                            ["Jenette", "Caldwell", "Development Lead", "New York", "3rd Sep 11", "$345,000"],
                            ["Jennifer", "Chang", "Regional Director", "Singapore", "14th Nov 10", "$357,650"]
                    ]
            }
         */
        
        /**
         * @param {jQuery} $element
         * @returns {Function}
         */
        function getRequestProcessor($element) {
            return function(data, callback, setting){
                console.log(data);
                var url = $element.data('url');
                var queryParams = {
                    action: $element.data('action') || 'list',
                    off: data.start,
                    cnt: data.length,
                    sdr: (data.order||[{}])[0].dir||'asc'
                };
                var sortCol = (data.order||[{}])[0].column;
                if (sortCol !== undefined) {
                    queryParams.srt = data.columns[sortCol].name;
                }
                console.log('queryParams', queryParams);
                var parser = document.createElement('a');
                parser.href = url;
                //TODO use https://github.com/sindresorhus/query-string
                parser.search = (parser.search.length === 0 ? '?' : '&') + $.param(queryParams);
                Moose.Util.ajaxServlet(parser.href, 'GET', data, getResponseProcessor(data, callback));
            };
        }
        
        function getResponseProcessor(requestData, callback) {
            return function(responseData) {
                console.log(responseData);
                var data = {
                    draw: requestData.draw,
                    recordsTotal: responseData.countTotal,
                    recordsFiltered: responseData.countTotal,
                    data: responseData.entity.map(function(entity){
                        return Object.values(entity.fields);
                    })
                };
                console.log(data);
                callback(data);
            };
        }
        
        function setupDatatable(element) {
            $(element).DataTable({
                processing: true,
                serverSide: true,
                columnDefs: [
                    {name: 'regDate', targets: 0},
                    {name: 'firstName', targets: 1},
                    {name: 'lastName', targets: 2},
                    {name: 'studentId', targets: 3}
                ],
                ajax: getRequestProcessor($(this))
            });
        }
        
        function onDocumentReady() {
            $('.moose-datatable').eachValue(setupDatatable);
        }
        
        return {
            onDocumentReady: onDocumentReady
        };
        
    })();
})(jQuery, window, window.Moose);