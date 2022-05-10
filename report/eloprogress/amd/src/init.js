/*
* Javascript needed for license tool datatables
* 
* @package    report_eloprogress
* @copyright  2020 ELO TECH
*/

define(['jquery',
        'report_eloprogress/jquery.dataTables',
        'report_eloprogress/dataTables.fixedHeader',
        'report_eloprogress/dataTables.fixedColumns',
       ], function ($) {
    return {
        init: function (identifier, config) {
            $(document).ready(function () {
                var table = $(identifier).DataTable(config);
                var fixed_top = $('.fixed-top').innerHeight();
                table.fixedHeader.headerOffset(fixed_top);
//                $('div#progress-window').hide();
            });
        }
    }
});