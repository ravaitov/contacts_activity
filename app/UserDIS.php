<?php

namespace App;

class UserDIS extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	ENTITY_ID id,
            	CONCAT(IFNULL(LAST_NAME, ''), ' ', NAME) name
            FROM
            	b_hr_structure_node_member hs
            	LEFT JOIN b_user u ON u.ID = hs.ENTITY_ID
            WHERE
            	NODE_ID IN (13, 14)
            	AND hs.`ACTIVE` = 'Y'
            order by name
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_NUM);
        foreach ($res as $el) {
            $this->result[$el[0]] = $el[1];
        }
//        print_r($this->result);
    }
}