<?php

namespace App;

class InputsData extends AbstractApp
{

    private array $weekList;
    private int $weekCnt;
    private string $date;

    private int $company;
    private string $prodName;
    private string $complect;
    private string $techType;
    private string $login;
    private string $fioOv;

    private array $current;
    private array $data;

    private string $noLogin = 'н/и';
    private string $noLogin2 = 'н/и КЦ';

    public function run(): void
    {
        $this->requestData();
        $this->initWeekList();
        $this->getIntputs();
        $this->fillWeekList();
    }

    public function weekList(): array
    {
        return array_keys($this->weekList);
    }

    public function weeksInputs(
        int    $company,
        string $prodName,
        string $complect,
        string $techType,
        string $login,
        string $fioOv
    ): array
    {
        $this->company = $company;
        $this->prodName = $prodName;
        $this->complect = $complect;
        $this->techType = $techType;
        $this->login = $login;
        $this->fioOv = $fioOv;

        $result = [];
        $total = null;
        foreach ($this->weekList as $week => $this->data) {
            $this->current = ['', '', ''];
//            $this->current = [$week, $company, $complect];
            switch ($techType) {
                case 'ОВМ':
                    $this->makeLoginOVM();
                    break;
                case 'сет':
                case 'с/м':
                    $this->makeLoginNet();
                    break;
            }
            if ($this->current[0] == 1 || $this->current[1] == 1) {
                $total = 1;
                $this->current[2] = 1;
            } elseif ($this->current[0] === 0 || $this->current[1] === 0) {
                $total = $total ?: 0;
                $this->current[2] = 0;
            } else {
                $total ??= $this->current[0] ?: $this->current[1];
                $this->current[2] = $total;
            }
            $result = array_merge($result, $this->current);
        }
        $result[] = $total;

        return $result;
    }

    private function makeLoginOVM()  //ОВМ
    {
        if (!$this->login) {
            $this->current[0] = $this->noLogin;
            return;
        }
        preg_match_all('|[\d_/]+|', $this->complect, $m);
        $login = str_replace(['_0', '/'], ['_', '_'], $m[0][0]) . "#$this->login";
        $this->current[0] = empty($this->data['login'][$login]) ? 0 : 1;
    }

    private function makeLoginNet()  //сет,  с/м
    {
        $this->current[0] = $this->login ? (empty($this->data['login'][$this->login]) ? 0 : 1) : $this->noLogin;
        $this->current[1] = $this->fioOv ? (empty($this->data['fio_ov'][$this->fioOv]) ? 0 : 1) : $this->noLogin;
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
        $this->weekCnt = (int)$weekCnt;
    }

    private function initWeekList(): void
    {
        $last = (int)strtotime($this->date);
        for ($i = $this->weekCnt - 1; $i >= 0; $i--) {
            $t = strtotime("-$i week", $last);
            $this->weekList[(int)date('W', $t)] = []; // int - чтобы не было 01 02 ...
        }
//        $this->log(print_r($this->weekList, 1));
    }

    private function getIntputs(): void
    {
        $date = str_replace('-', '', $this->date);
        $cacheFile = $this->config->conf('stor_dir') . "cache/{$this->weekCnt}_$date";
        if (is_file($cacheFile)) {
            $this->result = unserialize(file_get_contents($cacheFile));
            return;
        }
        $sql = <<<SQL
            SET DATEFIRST 1;
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
            	idCompany IS NOT NULL;			
            SQL;
        $this->result = $this->baseMs->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        file_put_contents($cacheFile, serialize($this->result));
    }

    private function fillWeekList(): void
    {
        foreach ($this->result as $item) {
            $this->weekList[$item['num_week']]['data'][] = [
                'company_id' => $item['company_id'],
                'product' => [$item['ide_product'], $item['tech_type'], $item['version'],],
                'cnt' => $item['cnt'],
                'login' => $item['login'],
                'fio_ov' => $item['fio_ov'],
            ];
        }
//        $this->log(print_r($this->weekList, 1));
        foreach ($this->weekList as &$week) {
            $this->createKeys($week);
        }
//        $this->log(print_r($this->weekList, 1));
    }

    private function createKeys(array &$week): void //
    {
        if (empty($week['data']))
            return;
        for ($i = count($week['data']) - 1; $i >= 0; $i--) {
            $el = $week['data'][$i];
            $week['company_product'][$el['company_id'] . '_' . $el['product'][0]][] = $i;
            if (!empty($el['fio_ov'])) {
                $week['fio_ov'][$el['fio_ov']][] = $i;
            }
//            unset($week['data'][$i]['fio_ov']);
            if (!empty($el['login'])) {
                $week['login'][$el['login']][] = $i;
                $login2 = explode('#', $el['login']);
                if (count($login2) > 1) {
                    $week['short_login'][$login2[0]][] = $i;
                }
            }
//            unset($week['data'][$i]['login']);
        }
    }
}