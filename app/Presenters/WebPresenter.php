<?php

namespace App\Presenters;

class WebPresenter extends AbstractPresenter
{
    public function sendTable(): string
    {
        return json_encode([
            'thead' => $this->thead(),
            'body' => $this->body(),
        ],
            JSON_UNESCAPED_UNICODE);
    }

    private function thead(): string
    {
        $res = "<tr>\n<th rowspan='3'>#</th>\n";
        foreach ($this->data['data'][0] as $field => $el) {
            if ($field[0] == '#')
                continue;
            if ($field == 'products') {
                foreach ($el[0] as $prod => $x) {
                    $res .= "<th rowspan='3'>{$this->fieldMapper[$prod]}</th>\n";
                }
                continue;
            }
            $res .= "<th rowspan='3'>{$this->fieldMapper[$field]}</th>\n";
        }
        $cnt = count($this->data['weeks']);
        $cols = $cnt * 3;
        $res .= "<th colspan='$cols'>недели</th>\n<th  rowspan='3'>Итог</tr\n><tr>\n";
        foreach ($this->data['weeks'] as $week) {
            $res .= "<th colspan='3'>$week</th>";
        }
        $res .= "</tr>\n<tr>\n";
        for ($i = 0; $i < $cnt; $i++) {
            $res .= "<th>Онлайн</th><th>Офлайн</th><th>Итог</th>";
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
        foreach ($this->data['data'] as $row) {
            $leftRow = '';
            foreach ($row as $field => $val) {
                if ($field[0] == '#')
                    continue;
                if ($field == 'products') {
                    foreach ($val as $prods) {
                        $prodStr = '';
                        foreach ($prods as $prodField) {
                            $prodStr .= "<td>$prodField</td>\n";
                        }
                    }
                    $i++;
                    $x = '';
                    for ($k = 0; $k < ($cnt * 3 + 1); $k++) {
                        $x .= "<td> </td>\n";
                    }
                    $res .= "<tr>\n<td>$i</td>\n$leftRow$prodStr$x</tr>";
                    continue;
                }
                $leftRow .= "<td>$val</td>\n";
            }
        }

        return $res;
    }
}