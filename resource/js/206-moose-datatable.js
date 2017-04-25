/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function($, window, Moose, undefined){   
    Moose.Datatable  = (function() {
        
        function setupDatatable(element) {
            $(element).DataTable({
                processing: true,
                serverSide: true,
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
                ajax: function(data, callback, setting) { 
                    callback({});
                }
            });
        }
        
        function onDocumentReady() {
            $('.moose-datatable').eachValue(setupDatatable);
        }
        
        return {
            ajaxServlet: ajaxServlet,
            onDocumentReady: onDocumentReady
        };
        
    })();
})(jQuery, window, window.Moose);