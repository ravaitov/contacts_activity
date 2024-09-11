<?php

namespace App;

use PDO;

class CompanyList extends AbstractApp
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
            $this->result[$el[0]] = $el[1];
        }
//        print_r($this->result);
    }
}