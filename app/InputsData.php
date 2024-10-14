<?php

namespace App;

class InputsData extends AbstractApp
{
    public array $weekList;

    private int $weekCnt;
    private string $date;

    public function run(): void
    {
        $this->requestData();
        $this->getWeekList();
//        $this->getIntputs();
    }

    private function requestData(): void
    {
        $weekCnt = $_REQUEST['week'] ?? 0;
        $date = $_REQUEST['date'] ?? 0;
        $this->log("Количество недель: $weekCnt, дата: $date");
        if (!$weekCnt || !$date || $weekCnt > 10 || !preg_match('/\d\d\d\d-\d\d-\d\d/', $date)) {
            throw new \Exception("Не корректные данные!\n" . print_r($_REQUEST, 1));
        }
        $this->date = $date;
        $this->weekCnt = $weekCnt;
    }

    private function getWeekList(): void
    {
        $week = 7 * 24 * 3600;
        $last = strtotime($this->date);
        for ($t = $last - $week * ($this->weekCnt - 1); $t <= $last; $t += $week) {
            $this->weekList[] = date('W', $t);
        }
//        $this->log(print_r($this->weekList, 1));
    }

    private function getIntputs(): void
    {
        $date = str_replace('-', '', $this->date);
        $sql = <<<SQL
            SELECT
            	idCompany company_id,
            	IdeProdukt ide_product,
            	IdeTechType tech_type,
            	IdeVer 'version',
            	NumWeek num_week,
            	NumYear num_year,
            	cnt,
            	login,
            	fio_ov
            FROM
            	dbo.uf_ric037_report_distr_log_b24 ($this->weekCnt, '$date')
            WHERE
            	idCompany IS NOT NULL			
            SQL;
        $res = $this->baseMs->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
//        $this->log(print_r($res, 1));
        $this->result  = $res;

    }
}