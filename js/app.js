document.addEventListener('DOMContentLoaded', () => {
    $('#xlsx').prop('disabled', true);
    renderDatetimepicker();
});

function send() {
    preloaderSwitch('on');
    $.ajax({
        type: "POST",
        url: "table.php",
        dataType: 'text',
        data: {
            "week": document.querySelector('#weekpicker').value,
            "date": $("#datetimepicker1").data('date')
        }, success: function (data) {
            $("#table1 tbody").empty();
            $("#table1 thead").empty();
            data = JSON.parse(data);
            $('#table1 tbody').append(data.body);
            $("#table1 thead").append(data.thead);


            $('#xlsx').prop('disabled', false);
            preloaderSwitch('off');
        },
        error: function (error) {
            // data = JSON.parse(data);
            alert('error1: ' + error.responseText);
        }
    });
}


function getUser() {
    $.get("https://bitrix.zemser.ru/local/b24_scripts/user.php",
        onAjaxSuccess
    );

    function onAjaxSuccess(data) {
        alert(data);
    }
}