function renderDatetimepicker() {
    d = new Date();
    d = d.setDate(1);
    $('#datetimepicker1').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ru',
        allowInputToggle: true,
        showTodayButton: true,
        icons: {
            today: 'today-button-pf'
        },
        defaultDate: d
    });
}

