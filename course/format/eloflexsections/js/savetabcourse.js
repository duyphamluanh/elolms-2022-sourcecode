$(document).ready(function () {
    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        localStorage.setItem('activeTab', $(e.target).attr('href'));
    });
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        $('#eloflexsectionsTab a[href="' + activeTab + '"]').tab('show');
        $('#eloflexsectionsTab a[href="' + activeTab + '"]').addClass('active show');
    } else {
        $('.nav-tabs a[href="#tabcontent"]').tab('show');
        $('#eloflexsectionsTab a[href="#tabcontent"]').addClass('active show');
    }
});