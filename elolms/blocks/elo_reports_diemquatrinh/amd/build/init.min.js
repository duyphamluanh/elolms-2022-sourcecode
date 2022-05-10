/*
 * Javascript needed for license tool datatables
 * 
 * @package    block_elo_reports_diemquatrinh
 * @copyright  2018 Kentucky Educational Television
 */

define(['jquery','core/ajax', 'core/str', 'core/notification', 'core/modal_factory', 'core/modal_events', 'core/templates'
], function ($, Ajax, Str, Notification, ModalFactory, ModalEvents, Templates) {
    // Needed to make DataTables export to Excel work
    // https://datatables.net/forums/discussion/33363/excel-export-doesnt-work
    /**
     * Selectors.
     *
     * @access private
     * @type {Object}
     */
    let SELECTORS = {
        BUTTON_EXPORT_TO_EXCEL: '#btnberexporttoexcel',
        BULKACTIONFORM: '#bercourseslmsform',
        BUTTONNEXT : '.chosen-select-diemquatrinh'
    };
    return {
        init: function () {
            $(document).ready(function () {
                let valuecourse, input = $(SELECTORS.BULKACTIONFORM + ' [name=id]');
                $(SELECTORS.BUTTON_EXPORT_TO_EXCEL).on('click', function () {
                    valuecourse = $(SELECTORS.BUTTONNEXT).val();
                    if (valuecourse.length >= 1) {
                        let params = Array.from(document.getElementById("diemquatrinhcourseid").options).filter(option => option.selected).map(option => option.value);
                        input.val(params.toString());
                        $(SELECTORS.BULKACTIONFORM).submit();
                    } else {
                        Notification.alert(
                                null, 'Bạn chưa chọn lớp!!!!'
                                );
                        return;
                    }
                }.bind(this));
            });
        }
    };
});
