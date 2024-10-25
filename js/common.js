function ajaxNow(url, data, callback = (text) => {
    console.log('AJAX returned:\n' + text)
}, async = true) {
    let xhr = new XMLHttpRequest(),
        body = makeHttpQueryString(data);
    xhr.open('POST', url, async);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (this.readyState != 4) return;
        if (this.responseText) {
            callback(this.responseText);
            return;
        }
        ;
        console.log('Something go wrong. There are problems with Ajax query');
    };
    xhr.send(body);
}

function makeHttpQueryString(obj) {
    let string = ``,
        i = 0;
    if (obj) {
        for (let key in obj) {
            if (i++) string += `&`;
            string += key + `=` + obj[key];
        }
    }
    return string;
}

function getJsonParse(json) {
    let result;
    try {
        result = JSON.parse(json);
        alertWarnings(result);
    } catch (e) {
        alertErrors(`Произошел сбой при получении данных с сервера. Попробуйте позже.`, e, json);
        return false;
    }
    return result;
}

function alertWarnings(result) {
    if (result['errors']) {
        for (let error of result['errors']) {
            alert(error);
            console.dir(result);
        }
    }
}

function alertErrors(message, errorData, text = false, data = false) {
    alert(message);
    alert(errorData);
    console.log('\nJSON.parse(answer) returned an error: ');
    console.dir(errorData);
    if (text) console.log(text);
    if (data) console.dir(data);
}

function preloaderSwitch(mode = 'auto') {
    let preloader = document.getElementById('preloader');
    if (mode === 'on') {
        preloader.style.display = 'block';
    } else if (mode === 'off') {
        preloader.style.display = 'none';
    } else if (mode === 'auto') {
        preloader.style.display = preloader.style.display == 'none' ? 'block' : 'none';
    }
}


function jump(id) {
    start = $("#datetimepicker0").data('date');
    end = $("#datetimepicker1").data('date');
    preloaderSwitch('on');
    win = window.open('https://app.zemser.ru/annual_report/report.php?id=' + id + '&start=' + start + '&end=' + end, "_blank");
    win.addEventListener('load', preloaderSwitch('off'), false);
}

function tableXls() {
    $("#table1").table2excel({
        exclude: ".excludeThisClass",
        name: "Worksheet1",
        filename: "file.xls", // do include extension
        preserveColors: false // set to true if you want background colors and font colors preserved
    });
}