<div id="preloader">
    <div class="spinner spinner-lg spinner-inverse"></div>
</div>
<div class="container">
    <h4>Отчет по входам</h4>
    <br>
    <div class="datepicker col-sm-8">
        <label for="weekpicker">Количество недель &nbsp;</label>
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
    <div class="col-sm-3">
        <input type="button" value="Скачать xlsx" onclick="alert('Клик!')" id="xlsx">
    </div>
    <div>
        <input type="button" value="Test" onclick="test()" id="test">
    </div>

</div>
<div>
    <table class="table" id="table1">
        <thead>
        <tr>
            <th>#</th>
            <th>Компания</th>
            <th>Активность</th>
            <th>Контакт</th>
            <th>Ключевой?</th>
            <th>36</th>
            <th>37</th>
            <th>38</th>
            <th>39</th>
            <th>40</th>
            <th>50</th>
            <th>51</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
