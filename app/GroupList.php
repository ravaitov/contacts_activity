<?php

namespace App;

class GroupList extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            select NAME
            from b_iblock_element
            where IBLOCK_ID = 67
        SQL;
        $result = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_NUM);
        foreach ($result as $el) {
            $this->result[$el[0]] = $el[0];
        }
//        print_r($this->result);
    }
}