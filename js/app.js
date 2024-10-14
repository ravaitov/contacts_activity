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
        error: function () {
            alert('error1');
        }
    });
}

function test() {
    $("#table1 thead").empty();
        str =
        '<tr>' +
        '<th>#</th>' +
        '<th>Компания</th>' +
        '<th>Активность</th>' +
        '<th>Контакт</th>' +
        '<th>Ключевой?</th>' +
        '<th>36</th>' +
        '<th>37</th>' +
        '<th>38</th>' +
        '<th>39</th>' +
        '<th>40</th>' +
        '<th>50</th>' +
        '<th>511111</th>' +
        '</tr>';
    $("#table1 thead").append(str);
}