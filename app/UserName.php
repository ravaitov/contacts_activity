<?php

namespace App;

    class UserName extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	ID id,
            	concat(ifnull(LAST_NAME, ''), ' ', NAME) name
            FROM
            	b_user
            WHERE
            	`ACTIVE` = 'Y'
                AND LID = 's1'
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_NUM);
        foreach ($res as $el) {
            $this->result[$el[0]] = $el[1];
        }
//        print_r($this->result);
    }
}