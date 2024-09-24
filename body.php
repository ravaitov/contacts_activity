<div id="preloader">
    <div class="spinner spinner-lg spinner-inverse"></div>
</div>
<div class="container">
    <h4>Использование систем и сервисные услуги</h4>
    <br>
    <div class="datepicker col-sm-8">
        <label for="datetimepicker0">От&nbsp;</label>
        <div class="input-group date-time-picker-pf col-sm-5" id="datetimepicker0">
            <input type="text" class="form-control">
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <br>
        <label for="datetimepicker1">до&nbsp;</label>
        <div class="input-group date-time-picker-pf col-sm-5" id="datetimepicker1">
            <input type="text" class="form-control">
            <span class="input-group-addon">
                <span class="fa fa-calendar"></span>
            </span>
        </div>
        <h4> &nbsp;&nbsp;</h4>
        <div class="container companies">
            <table  class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Компания</th>
                    <th>ID</th>
                </tr>
                </thead>
                <tbody>
                <?php
                include '/home/worker/https/annual_report/companies.php';
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Blank Slate HTML -->
    <div class="blank-slate-pf table-view-pf-empty hidden" id="emptyState1">
        <div class="blank-slate-pf-icon">
            <span class="pficon pficon pficon-add-circle-o"></span>
        </div>
        <h1>
            Нет данных
        </h1>
        <p>
            Попробуйте позже, либо, если неисправность наблюдается вами в течении длительного времени, обратитесь в техническую поддержку.
        </p>
    </div>
</div>
<div class="container">
    <ul>
        <li>поиск по названию Компании (или ID) - Ctrl-F (F3 - следующий поиск)</li>
        <li>клик на компанию запускает обработку отчета для этой компании по указанному периоду</li>
        <li>обработка отчета длится 10 - 20 секунд и  загружается файл xlsx</li>
        <li>нельзя запускать одновременно 2 и более отчетов - будет перегрузка системы</li>
        <li>если для данной компании уже был отчет с таким же периодом - результат получится мгновенно из кэша</li>
    </ul>
</div>
