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
        $res = "<tr>\n<th>#</th>\n";
        foreach ($this->data[0] as $field => $el) {
            if ($field[0] == '#')
                continue;
            if ($field == 'products') {
                foreach ($el[0] as $prod => $x) {
                    $res .= "<th>{$this->fieldMapper[$prod]}</th>\n";
                }
                continue;
            }
            $res .= "<th>{$this->fieldMapper[$field]}</th>\n";
        }
        $res .= "</tr>\n";
//        $this->log(print_r($res, 1));

        return $res;
    }

    private function body(): string
    {
        $i = 0;
        $res = "";
        foreach ($this->data as $row) {
            $leftRow = '';
            foreach ($row as $field => $val) {
                if ($field[0] == '#')
                    continue;
                if ($field == 'products') {
                    foreach ($val as  $prods) {
                        $prodStr = '';
                        foreach ($prods as $prodField) {
                            $prodStr .= "<td>$prodField</td>\n";
                        }
                    }
                    $i++;
                    $res .= "<tr>\n<td>$i</td>\n$leftRow$prodStr</tr>";
                    continue;
                }
                $leftRow .= "<td>$val</td>\n";
            }
        }

        return $res;
    }
}