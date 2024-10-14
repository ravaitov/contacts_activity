<?php

namespace App;

use PDO;

class CompanyName extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	TITLE,
            	c.id id
            FROM
            	b_crm_company c
            	JOIN b_uts_crm_company u ON c.ID = u.VALUE_ID
            WHERE
            	u.UF_CRM_1524464429 = 68
            ORDER BY
            	TITLE
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(PDO::FETCH_NUM);
        foreach ($res as $el) {
            $this->result[$el[1]] = $el[0];
        }
//        print_r($this->result);
    }
}