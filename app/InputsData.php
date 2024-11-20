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

    private string $noLogin = 'н/и';        // !!есть в body.php
    private string $noInfo = 'н/д КЦ';      // !!есть в body.php

    public function run(): void
    {
//        $this->requestData();
        $this->initWeekList();
        $this->getIntputs();
        $this->fillWeekList();
    }

    public function weekNums(): array
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
            switch ($techType) {
                case '':
                    break;
                case 'ОВМ':
                    $this->processingLoginOVM();
                    break;
                case 'сет':
                case 'с/м':
                    $this->processingLoginNet();
                    break;
                case 'ОВП':
                    $this->processingOVP();
                    break;
                default:
                    $this->processingOther();
            }
            if ($this->current[0] == 1 || $this->current[1] == 1) {
                $total ??= 1;
                $this->current[2] = 1;
            } elseif ((string)$this->current[0] === '0' || (string)$this->current[1] === '0') {
                $total = 0;
                $this->current[2] = 0;
            } else {
                $this->current[2] = $this->current[0] ?: $this->current[1];
                $total ??= $this->current[2];
            }
            $result = array_merge($result, $this->current);
        }
        $result[] = $total;

        if ($this->blocked('total', $total))
            return [];

        return $result;
    }

    private function processingLoginOVM(): void  //ОВМ
    {
        if (!$this->login) {
            $this->current[0] = $this->noLogin;
            return;
        }
        preg_match_all('|[\d_/]+|', $this->complect, $m);
        $login = str_replace(['_0', '/'], ['_', '_'], $m[0][0]) . "#$this->login";
        if ($el = $this->data['login'][$login] ?? false) {
            $this->current[0] = $this->data['data'][$el[0]]['cnt'] ? 1 : 0;
        } else {
            $this->current[0] = $this->noInfo;
        }
    }

    private function processingLoginNet(): void  //сет,  с/м
    {
        if (!$this->login) {
            $this->current[0] = $this->noLogin;
        } elseif ($el = $this->data['login'][$this->login] ?? false) {
            $this->current[0] = $this->data['data'][$el[0]]['cnt'] ? 1 : 0;
        } else {
            $this->current[0] = $this->noInfo;
        }

        if (!$this->fioOv) {
            $this->current[1] = $this->noLogin;
        } elseif ($el = $this->data['fio_ov'][$this->fioOv] ?? false) {
            $this->current[1] = $this->data['data'][$el[0]]['cnt'] ? 1 : 0;
        } else {
            $this->current[1] = $this->noInfo;
        }
    }

    private function processingOVP(): void //ОВП
    {
        if (!$list = $this->data['company_complect'][$this->company . '_' . $this->complect] ?? []) {
            $this->current = [$this->noInfo, $this->noInfo, $this->noInfo,];
            return;
        }
        foreach ($list as $i) {
            $el = $this->data['data'][$i];
            if ($el['tag']) { // online
                if ($this->getLogin() ==  $el['login']) {
                    $this->current[0] = $el['cnt'] ? 1 : 0;
                }
            }
        }
    }

    private function processingOther(): void //И-В,ОВК,ОВК-Ф,ОВМ,ОВМ2,ОВМ3,ОВМ-Ф
    {
        if (!$list = $this->data['company_complect'][$this->company . '_' . $this->complect] ?? []) {
            $this->current = [$this->noInfo, $this->noInfo, $this->noInfo,];
            return;
        }
        foreach ($list as $i) {
            $el = $this->data['data'][$i];
            if ($el['tag']) { // online
                if ($this->getLogin() == explode('#', $el['login'])[0]) {
                    $this->current[0] = $el['cnt'] ? 1 : 0;
                }
            } else { // offlint
                $this->current[1] = $el['cnt'] ? 1 : 0;
            }
            $this->current = array_map(fn($x) => $x === '' ? $this->noInfo : $x, $this->current);
        }
    }

    private function getLogin(): string
    {
        preg_match_all('|[\d_/]+|', $this->complect, $m);
        return str_replace(['_0', '/'], ['_', '_'], $m[0][0]);
    }

    private function initWeekList(): void
    {
//        $this->log('static__ ' . print_r(static::$params, 1));
        $this->date = static::$params['date'];
        $this->weekCnt = static::$params['week'];
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
        $cacheFile = $this->cacheFile();
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
            	fio_ov,
            	tag,
            	ComplREG complect
            FROM
            	dbo.uf_ric037_report_distr_log_b24 ($this->weekCnt, '$date')
            WHERE
            	idCompany IS NOT NULL
                AND ComplREG IS NOT NULL;			
            SQL;
        $this->result = $this->baseMs->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        file_put_contents($cacheFile, serialize($this->result));
    }

    private function fillWeekList(): void
    {
        foreach ($this->result as $item) {
            if (empty($item['num_week']))
                continue;
            $this->weekList[$item['num_week']]['data'][] = [
                'company_id' => $item['company_id'],
                'product' => [$item['ide_product'], $item['tech_type'], $item['version'],],
                'cnt' => $item['cnt'],
                'login' => $item['login'],
                'fio_ov' => $item['fio_ov'],
                'tag' => $item['tag'],
                'complect' => $item['complect'],
            ];
        }
//        $this->log(print_r($this->weekList, 1));
        foreach ($this->weekList as &$week) {
            $this->createKeys($week);
        }
//        $this->log(print_r($this->weekList, 1));
    }

    private function createKeys(array &$week): void // доп. массивы с ключами fio_ov, login и company_product  для быстрого поиска
    {
        if (empty($week['data']))
            return;
        for ($i = count($week['data']) - 1; $i >= 0; $i--) {
            $el = $week['data'][$i];
            $week['company_complect'][$el['company_id'] . '_' . $el['complect']][] = $i;
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