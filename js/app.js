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
            "date": $("#datetimepicker1").data('date'),
            "sds": $("#sds")[0].value,
            "contact": $("#contact")[0].value,
            "ois": $("#ois")[0].value,
            "total": $("#total")[0].value,
            "dis": $("#dis")[0].value,
            "group": $("#group")[0].value,
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

function tableXls() {
    preloaderSwitch('on');

    let week = document.querySelector('#weekpicker').value;
    let date = $("#datetimepicker1").data('date');

    let formData = new FormData();
    formData.append('week', week);
    formData.append('date', date);
    formData.append('sds', $("#sds")[0].value);
    formData.append('contact', $("#contact")[0].value);
    formData.append('ois', $("#ois")[0].value);
    formData.append('total', $("#total")[0].value);
    formData.append('dis', $("#dis")[0].value);
    formData.append('group', $("#group")[0].value);
    formData.append('xlsx', 1);

    let url = 'https://app.zemser.ru/reports/contacts_activity/table.php';
    let request = new XMLHttpRequest();
    request.open('POST', url, true);
    request.responseType = 'blob';

    request.onload = function(e) {
        preloaderSwitch('off');
        if (this.status === 200) {
            let blob = this.response;
            let fileName = 'report_' + week + '_' + date + '.xlsx';
            if(window.navigator.msSaveOrOpenBlob) {
                window.navigator.msSaveBlob(blob, fileName);
            }
            else{
                var downloadLink = window.document.createElement('a');
                var contentTypeHeader = request.getResponseHeader("Content-Type");
                downloadLink.href = window.URL.createObjectURL(new Blob([blob], { type: contentTypeHeader }));
                downloadLink.download = fileName;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            }
        }
    };
    request.send(formData);
}

