function renderDatetimepicker() {
    let d = new Date();
    d = d.setYear(d.getFullYear() - 1);
    $('#datetimepicker0').datetimepicker({
        format: 'YYYY-MM',
        locale: 'ru',
        allowInputToggle: true,
        showTodayButton: true,
        icons: {
            today: 'today-button-pf'
        },
        defaultDate: d
    });
    d = new Date();
    d = d.setMonth(d.getMonth() - 1);
    $('#datetimepicker1').datetimepicker({
        format: 'YYYY-MM',
        locale: 'ru',
        allowInputToggle: true,
        showTodayButton: true,
        icons: {
            today: 'today-button-pf'
        },
        defaultDate: d
    });
}