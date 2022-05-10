$(function () {
    let config = {
        '.chosen-select': {width: '30%'},
        '.chosen-select-diemquatrinh': {width: '100%',max_selected_options: 50}
    }
    for (let selector in config) {
        $(selector).chosen(config[selector]);
    }
    $(".chosen-select-diemquatrinh").bind("chosen:maxselected", function () {
        alert("Bạn chỉ được chọn tối đa 50 lớp!!!");
    });

    $(document).ready(function () {
        $("select#crformat").change(function () {
            if ($('#btneloreports').length)
                $("#btneloreports").attr('title', $(this).find('option:selected').text());
            else
                $("#btneloreportsview").attr('title', $(this).find('option:selected').text());
        });
    });
});
