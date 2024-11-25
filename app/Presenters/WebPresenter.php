<?php

namespace App\Presenters;

class WebPresenter extends AbstractPresenter
{
    public function sendTable(): void
    {
        $json = json_encode([
            'thead' => $this->thead(),
            'body' => $this->body(),
        ],
            JSON_UNESCAPED_UNICODE);
//        $this->log(print_r(json_decode($json, 1), 1));
        echo $json;
    }

    private function thead(): string
    {
        $res = "<tr>\n<th rowspan='3'>#</th>\n";
        foreach ($this->fieldMapper as $el) {
            $res .= "<th rowspan='3'>$el</th>\n";
        }
        $cnt = count($this->data['weeks']);
        $cols = $cnt * 3;
        $res .= "<th colspan='$cols'>недели</th>\n<th rowspan='3'>&nbsp;&nbsp;Итог&nbsp;&nbsp;</th><th rowspan='3'>Итог пользователя</th>\n</tr>\n";
        foreach ($this->data['weeks'] as $week) {
            $res .= "<th colspan='3'>$week</th>";
        }
        $res .= "</tr>\n<tr>\n";
        for ($i = 0; $i < $cnt; $i++) {
            $res .= "<th>Онлайн</th><th>Офлайн</th><th>&nbsp;&nbsp;Итог&nbsp;&nbsp;</th>";
        }
        $res .= "</tr>\n";
//        $this->log(print_r($res, 1));

        return $res;
    }

    private function body(): string
    {
        $i = 0;
        $res = "";
        $cnt = count($this->data['weeks']);
        if (!$this->data['data'])
            return '0';
        foreach ($this->data['data'] as $row) {
            $leftRow = '';
            $error = '';
            foreach ($row as $key => $val) {
                if ("$key"[0] == '#')
                    continue;
                if ($key == 'products') { // расщепление по продуктам
                    if ($val[0]['ide_product'] == '-') {
                        $error = " style=\"color:red\"";
                    }
                    foreach ($val as $prods) {
                        $prodStr = '';
                        foreach ($prods as $key => $prodField) {
//                            if (empty($this->fieldMapper[$key])) // скрыть логины
                            if (in_array($key, ['login', 'fio4ois'])) // скрыть логины
                                continue;
                            $prodStr .= "<td>$prodField</td>\n";
                        }
                        $i++;
                        $res .= "<tr$error>\n<td>$i</td>\n$leftRow$prodStr</tr>";
                    }
                } else { // это ловится до products
                    if (is_array($val)) {
                        $val = $val['web'];
                    }
                    $leftRow .= "<td>$val</td>\n";
                }
            }
        }

        return $res;
    }
}