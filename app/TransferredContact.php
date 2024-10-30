<?php

namespace App;

class TransferredContact extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	iep3.VALUE AS contact_id,
            	CONCAT(IFNULL(con.LAST_NAME, ''), ' ',  con.NAME, ' ', IFNULL(con.SECOND_NAME, '')) name
            FROM
            	b_iblock_element ie
            
            	INNER JOIN b_iblock_element_property iep ON iep.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep.IBLOCK_PROPERTY_ID = 699 -- Статус
            	AND iep.`VALUE` = 'Передан'
            
            	INNER JOIN b_iblock_element_property iep3 ON iep3.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep3.IBLOCK_PROPERTY_ID = 653 -- Контакт
            
            	INNER JOIN b_crm_contact con ON con.ID = iep3.VALUE
            WHERE ie.IBLOCK_ID = 90
            order by name
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            if (empty($el['name']) || empty($el['contact_id']))
                continue;
            $this->result[$el['contact_id']] = $el['name'];
        }
//        print_r($this->result);
    }
}