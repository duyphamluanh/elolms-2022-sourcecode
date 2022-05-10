/*
* Javascript needed for license tool datatables
* 
* @package    block_elo_reports_diemquatrinh
* @copyright  2018 Kentucky Educational Television
*/

define(['jquery',
        'block_elo_reports_diemquatrinh/jquery.dataTables',
        'block_elo_reports_diemquatrinh/jszip',
        'block_elo_reports_diemquatrinh/pdfmake',
        'block_elo_reports_diemquatrinh/dataTables.buttons',
        'block_elo_reports_diemquatrinh/buttons.html5',
        'block_elo_reports_diemquatrinh/buttons.print',
        'block_elo_reports_diemquatrinh/buttons.colVis',
        'block_elo_reports_diemquatrinh/dataTables.fixedHeader',
        'block_elo_reports_diemquatrinh/dataTables.fixedColumns',
        'block_elo_reports_diemquatrinh/dataTables.select',
        'block_elo_reports_diemquatrinh/dataTables.colReorder',
         'core/ajax', 'core/str', 'core/notification', 'core/modal_factory', 'core/modal_events', 'core/templates'
       ], function ($, datatables, jszip,
        pdfmake, buttons, buttonshtml5, buttonsprint, buttonscolVis, dtfixedHeader, dtfixedColumns, dtselect, dtcolReorder,
        Ajax, Str, Notification, ModalFactory, ModalEvents, Templates) {
    // Needed to make DataTables export to Excel work
    // https://datatables.net/forums/discussion/33363/excel-export-doesnt-work
    window.JSZip = jszip;

    /**
     * Selectors.
     *
     * @access private
     * @type {Object}
     */
    var SELECTORS = {
        BUTTON_EXPORT_TO_EXCEL: '#btnberexporttoexcel',
        TITLE_EXPORT: '#titleeloreports',
        BULKACTIONFORM: '#bercourseslmsform',
        BUTTON_FILTER_VIEW: '#btneloreportsview',

        TABLE_DATA_GIRD: '#elo_reports_datagird',
        TABLE_DATA_RECORDS: '#table_elo_reports',
        TABLE_DISPLAY_LENGTH: '#berlength',
        TABLE_PAGE: '#berpage',
        TABLE_SORT: '#bersort',

        SELECT_REPORT_FORMAT_EXPORT: '#crformatexport',
        SELECT_REPORT_FORMAT: '#crformat',
        SELECTCOURSEID: '#selectcourseid',
        SELECTTEACHERID: '#selectteacherid',
        SELECTSTUDENTID: '#selectstudentid',

        COURSEID: '#bercourseid',
        TEACHERID: '#berteacherid',
        STUDENTID: '#berstudentid',

    };

    var VARIBLES = {
        RECORDS_PER_LENGTH: 25,
        RECORDS_LAST_PAGE: 0,
        RECORDS_CURRENT_PAGE: 0,
        RECORDS_TOTAL : 0,
    }

    /**
     * viewAjax in the block_elo_reports_diemquatrinh.
     *
     * @method viewAjax
     * @private
     */
    var updatefiltdate = function() {
        $(SELECTORS.BUTTON_EXPORT_TO_EXCEL).attr("title",$(SELECTORS.SELECT_REPORT_FORMAT).find('option:selected').text());
        $(SELECTORS.TITLE_EXPORT).text($(SELECTORS.SELECT_REPORT_FORMAT).find('option:selected').text());
        $(SELECTORS.SELECT_REPORT_FORMAT_EXPORT).val($(SELECTORS.SELECT_REPORT_FORMAT).val());

        var berstartdate = document.getElementById("berstartdate");
        var berenddate = document.getElementById("berenddate");

        berstartdate.value = ($("#id_startdate_year").val() + '-' + $("#id_startdate_month").val() + '-' + $("#id_startdate_day").val());
        berenddate.value = ($("#id_enddate_year").val() + '-' + $("#id_enddate_month").val() + '-' + $("#id_enddate_day").val());

    };

    var getelefromattr = function(cell){
        var html = '';
        if(cell.attr){
            if(cell.attr.href){
                var title = (cell.attr.title ? cell.attr.title : cell.value);
                html = '<a href="'+cell.attr.href+'" title="'+cell.value+'" target="_blank">' +cell.value+'</a>';
            }
            else if(cell.attr.title){
                html = '<span title="'+cell.attr.title+'">'+cell.value+'<span>';
            }
            else {
                html = cell.value;
            }
        }
        else html = cell.value;
        return html;
    }

    $.fn.DataTable.ext.pager.simple_numbers_no_ellipses = function(page, pages){
       var numbers = [];
       var buttons = $.fn.DataTable.ext.pager.numbers_length;
       var half = Math.floor( buttons / 2 );
     
       var _range = function ( len, start ){
          var end;
        
          if ( typeof start === "undefined" ){
             start = 0;
             end = len;
     
          } else {
             end = start;
             start = len;
          }
     
          var out = [];
          for ( var i = start ; i < end; i++ ){ out.push(i); }
        
          return out;
       };
         
     
       if ( pages <= buttons ) {
          numbers = _range( 0, pages );
     
       } else if ( page <= half ) {
          numbers = _range( 0, buttons);
     
       } else if ( page >= pages - 1 - half ) {
          numbers = _range( pages - buttons, pages );
     
       } else {
          numbers = _range( page - half, page + half + 1);
       }
     
       numbers.DT_el = 'span';
     
       return [ 'previous', numbers, 'next' ];
    };

    $.fn.DataTable.ext.pager.full_numbers_no_ellipses = function(page, pages){
        pages = VARIBLES.RECORDS_LAST_PAGE;
        page = VARIBLES.RECORDS_CURRENT_PAGE;

        var lastpage = VARIBLES.RECORDS_LAST_PAGE ;
        var currentpage = VARIBLES.RECORDS_CURRENT_PAGE ;

        $.fn.DataTable.ext.pager.numbers_length =  VARIBLES.RECORDS_LAST_PAGE;

       var numbers = [];
       var buttons = $.fn.DataTable.ext.pager.numbers_length;
       var half = Math.floor( buttons / 2 );

     
       var _range = function ( len, start ){
          var end;
        
          if ( typeof start === "undefined" ){
             start = 0;
             end = len;
     
          } else {
             end = start;
             start = len;
          }
     
          var out = [];
                        
          // for ( var i = start ; i < end; i++ ){ out.push(i); }
          var pushdoc = true;
          for ( var page = start ; page < end; page++ ){ 
            if (page == currentpage){
                out.push(page);
            }
            else if (page == currentpage - 1 || page == currentpage - 2 || page == currentpage - 3){
                out.push(page);
            }
            else if (pushdoc && (page >= currentpage + 3 || (currentpage + 3 >= lastpage))){
                pushdoc = false;
                out.push("...");
            }
            else if ((page == currentpage + 1 || page == currentpage + 2) || page == lastpage){
                out.push(page);
            }
            else if (page == lastpage - 1){
                out.push(page);
            }
          }
        
          return out;
       };
     
       if ( pages <= buttons ) {
          numbers = _range( 0, pages );
     
       } else if ( page <= half ) {
          numbers = _range( 0, buttons);
     
       } else if ( page >= pages - 1 - half ) {
          numbers = _range( pages - buttons, pages );
     
       } else {
          numbers = _range( page - half, page + half + 1);
       }
     
       numbers.DT_el = 'span';
     
       return [ 'first', 'previous', numbers, 'next', 'last' ];
    };

    var tryParseInt = function (str) {
         var retValue = str;
         if(str !== null) {
             if(str.length > 0) {
                 if (!isNaN(str)) {
                     retValue = parseInt(str);
                 }
             }
         }
         return retValue;
    }

    var createTable = function(data){

        var datagird = $(SELECTORS.TABLE_DATA_GIRD);
        var title = '<div class="info">'+data['title']+'</div>';

        // var table = '<table id="table_elo_reports" class="flexible display">';
        var idTableHash  = SELECTORS.TABLE_DATA_RECORDS;
        var idTable  = SELECTORS.TABLE_DATA_RECORDS.replace("#","");
        var idTableFilter  = SELECTORS.TABLE_DATA_RECORDS + "_filter";
        var idTablePaginate = SELECTORS.TABLE_DATA_RECORDS + "_paginate";
        var idTableInfo = SELECTORS.TABLE_DATA_RECORDS + "_info";

        var table = '<table id="'+idTable+'" class="flexible display cell-border">';

        //header
        var colname = data['header'][1];
        var colattr = data['header'][0];
        var thead = '<thead><tr>';
        for(var col in colname){
            // var td = '<th class="header" sort="'+colattr[col]+'">'+colname[col]+'</th>';
            var typesort = data['bersort'].replace(colattr[col] + " ", "");
            var cls = "sorting";
            if(data['bersort'].length != typesort.length){
                cls += "_" + typesort;
            }
            var td = '<th sort="'+colattr[col]+'" class="'+cls+'">'+colname[col]+'</th>';
            thead += td;
        }
        thead += '</tr></thead>';
        table += thead;

        //body
        var records = data['records'];
        var tbody = '</tbody>';
        for(var row in records){
            tbody += '<tr>';
            for(var cell in records[row]){

                var html = getelefromattr(records[row][cell]);

                var td = '<td class="cell">'+html+'</td>';
                tbody += td;
            }
            tbody += '</tr>';
        }
        tbody += '</tbody>';
        table += tbody;

        table += '</table>';


        datagird.empty();
        datagird.append(title).append('<div class="list">'+table+'</div>');


        VARIBLES.RECORDS_TOTAL = tryParseInt(data['totalrecords']);
        VARIBLES.RECORDS_PER_LENGTH = tryParseInt(data['berlength']);
        VARIBLES.RECORDS_CURRENT_PAGE = tryParseInt(data['berpage']);

        var numbers_length = (VARIBLES.RECORDS_TOTAL/VARIBLES.RECORDS_PER_LENGTH);
        if(numbers_length != Math.floor(numbers_length)){
            numbers_length = Math.floor(numbers_length) + 1;
        }

        VARIBLES.RECORDS_LAST_PAGE = numbers_length;

        var lastpage = VARIBLES.RECORDS_LAST_PAGE ;
        var currentpage = VARIBLES.RECORDS_CURRENT_PAGE ;
        var totalrecords = VARIBLES.RECORDS_TOTAL ;
        var pagelength = VARIBLES.RECORDS_PER_LENGTH ;

        var config = {
            "lengthChange": false,
            "pagingType": "full_numbers_no_ellipses",
            // "displayStart": (currentpage*pagelength),
            "pageLength": data['berlength'],
            "ordering" : false,
            "initComplete": function(settings, json){
                var elements = $(idTablePaginate +" a.paginate_button");
                var maxtotal = (pagelength*(currentpage+1));
                var textinfo = ("Showing "+((pagelength * currentpage) + 1) +" to "+(maxtotal > totalrecords ? totalrecords : maxtotal)+" of "+totalrecords+" entries");
                $(idTableInfo).text(textinfo);

                for(var i = 0 ; i < elements.length ; i++){
                    var ele = $(elements[i]);
                    var txt = ele.text();

                    if(ele.hasClass("current")){
                        ele.removeClass("current");
                    }

                    if(tryParseInt(txt) == currentpage + 1){
                        // $(idTablePaginate + " a.paginate_button").removeClass("current");
                        ele.addClass("current");
                    }

                    if(txt.indexOf("...") !== -1){
                        ele.text("...");
                        ele.attr("id",idTable+"_0");
                    }
                }


                if(lastpage > 0 && currentpage > 0){
                    $(idTablePaginate + " a.paginate_button.first").removeClass("disabled");
                }

                if(lastpage > 0 && currentpage > 1){
                    $(idTablePaginate + " a.paginate_button.previous").removeClass("disabled");
                }

                if(lastpage > 0 && currentpage< lastpage - 1){
                    $(idTablePaginate + " a.paginate_button.last").removeClass("disabled");
                }

                if(lastpage > 0 && currentpage < lastpage - 2){
                    $(idTablePaginate + " a.paginate_button.next").removeClass("disabled");
                }
            },
            drawCallback: function(){
              $(idTablePaginate + ' a.paginate_button', this.api().table().container())
                .on('click', function(){
                    var ele = $(this);
                    var txt = ele.text();
                    if(txt.indexOf("...") === -1 && !$(this).hasClass("disabled") && !$(this).hasClass("current")){
                        $(idTableInfo).text("...");
                        $(idTable+"_0").text("...");

                        if(ele.hasClass('next')){
                            txt = currentpage + 1;
                        }else if(ele.hasClass('previous')){
                            txt = currentpage - 1;
                        } else if(ele.hasClass('first')){
                            txt = 0;
                        } else if(ele.hasClass('last')){
                            txt = lastpage - 1;
                        }else txt = (tryParseInt(txt) -1);

                        $(SELECTORS.TABLE_PAGE).val(txt);
                        viewAjax();
                    }else {
                        var maxtotal = (pagelength*(currentpage+1));
                        var textinfo = ("Showing "+((pagelength * currentpage) + 1) +" to "+(maxtotal > totalrecords ? totalrecords : maxtotal)+" of "+totalrecords+" entries");
                        $(idTableInfo).text(textinfo);
                    }
                    return false;
                 });
                 
                
              $(idTableHash + ' th.sorting', this.api().table().container())
                .on('click', function(){
                    var ele = $(this);
                    var colname = ele.attr('sort');
                    $(SELECTORS.TABLE_SORT).val(colname + ' asc');
                    viewAjax();
               });  
                
              $(idTableHash + ' th.sorting_asc', this.api().table().container())
                .on('click', function(){
                    var ele = $(this);
                    var colname = ele.attr('sort');
                    $(SELECTORS.TABLE_SORT).val(colname + ' desc');
                    viewAjax();
               });  
                
              $(idTableHash + ' th.sorting_desc', this.api().table().container())
                .on('click', function(){
                    var ele = $(this);
                    var colname = ele.attr('sort');
                    $(SELECTORS.TABLE_SORT).val(colname + ' asc');
                    viewAjax();
               });  
            }
        };
        var table = $(SELECTORS.TABLE_DATA_RECORDS)
        .on( 'order.dt',  function () { 
            return false;
         } )
        // .on( 'search.dt', function () {  } )
        .on( 'page.dt',   function () {
            // var pagecur = table.page.info();
            return false;
         } )
        .DataTable(config);
    }

    /**
     * viewAjax in the block_elo_reports_diemquatrinh.
     *
     * @method viewAjax
     * @private
     */
    var viewAjax = function() {
         // Generate a loading spinner while we're working.
        Templates.render('core/loading', {}).then(function(html) {
            // Append the loading spinner to the trigger element.
            $("#elo_reports_datagird").prepend('<div id="eloreportsloading">'+html+'</div>');
            // $(SELECTORS.BUTTON_FILTER_VIEW).prepend(html);
        }).then(function(results) {//Assign Form by Id to a Variabe
            updatefiltdate();
            
            var argsForm = {};
            var idele = ['crformat','bersemestersearch','bercourseid','berteacherid','bereditingteacherid','berstudentid','berstartdate','berenddate','berlength','berpage','bersemestercode','bersort'];

            for (var i = 0; i < idele.length; i++) {
                document.getElementById("bercourseslmsform").elements.namedItem(idele[i]).value = $('#'+idele[i]).val();
                argsForm[idele[i]] = $('#'+idele[i]).val();
            }

            var request = {
                methodname: 'block_elo_reports_getviewajax',
                args: argsForm ,
            };

            Ajax.call([request])[0].then(function(response) {
                var elodata = JSON.parse(response.elodata);
                createTable(elodata);
                return;
            }).catch(function(ex){
                Notification.exception(ex);
                $('#eloreportsloading').remove();
                return;
            });
            return;
        }).catch(function(ex){
            Notification.exception(ex);
            $('#eloreportsloading').remove();
        });
    };
    
    return {
         
        /*
        *   Converts specified table into a DataTable using provided configuration
        *
        *   @string     identifier  identifier of table to be converted
        *   @array      config      json_encoded array of configuration options
        *   @integer    childrow    column number of information to be shown in child row (optional)
        */
        
        init: function (identifier, config) {
            $(document).ready(function () {
                $(SELECTORS.BUTTON_EXPORT_TO_EXCEL).on('click', function() {
                    $(SELECTORS.BULKACTIONFORM).submit();
                }.bind(this));

                $(SELECTORS.BUTTON_FILTER_VIEW).on('click', function() {
                    viewAjax();
                });
                

            });
        }
    };
});