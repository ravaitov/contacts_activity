<div id="preloader">
    <div class="spinner spinner-lg spinner-inverse"></div>
</div>
<div class="main">
    <h3>&nbsp;&nbsp;&nbsp;Отчет по входам</h3>
    <br>
    <div class="datepicker col-sm-4">
        <label for="weekpicker">&nbsp;&nbsp;&nbsp;Количество недель &nbsp;</label>
        <input type="number" max="10" min="1" id="weekpicker" value="4">
        <label for="datetimepicker1">&nbsp; до &nbsp;</label>
        <div class="input-group date-time-picker-pf col-sm-5" id="datetimepicker1">
            <input type="text" class="form-control">
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <input type="button" value="Ok" id="apply" onclick="send()">
    </div>
    <div class="col-sm-6">
        <input type="button" value="Скачать xlsx" onclick="tableXls()" id="xlsx">
    </div>
    <div class="col-xs-6">
        &nbsp;
    </div>
    <div>
        <?php
        (new \App\Filter('sds', 'СДС', new \App\UserDIS()))->run(true);
        (new \App\Filter('contact', 'Контакты', new \App\TransferredContact()))->run(true);
        (new \App\Filter('ois', 'ОИС', ['оис']))->run(true);
        (new \App\Filter('total', 'Итог', ['0', '1', 'н/и' => 'н/и', 'н/и КЦ' => 'н/и КЦ']))->run(true);
        ?>
    </div>
</div>
<div>
    <table class="table" id="table1">
        <thead>
        <tr>
            <th rowspan='3'>#</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
