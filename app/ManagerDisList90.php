<?php

namespace App;

class ManagerDisList90 extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	iep2.VALUE AS company_id,
            	iep3.VALUE AS contact_id,
            	iep5.VALUE AS sds_id
            FROM
            	b_iblock_element ie
            
            	INNER JOIN b_iblock_element_property iep ON iep.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep.IBLOCK_PROPERTY_ID = 699 -- Статус
            	AND iep.`VALUE` = 'Передан'
            
            	INNER JOIN b_iblock_element_property iep2 ON iep2.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep2.IBLOCK_PROPERTY_ID = 667 -- Компания
            
            	INNER JOIN b_iblock_element_property iep3 ON iep3.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep3.IBLOCK_PROPERTY_ID = 653 -- Контакт
            
            	INNER JOIN b_iblock_element_property iep5 ON iep5.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep5.IBLOCK_PROPERTY_ID = 1286 -- СДС
            
            WHERE ie.IBLOCK_ID = 90	
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            if (empty($el['company_id']) || empty($el['contact_id']))
                continue;
            $this->result[$el['company_id']][$el['contact_id']] = $el['sds_id'];
        }
//        print_r($this->result);
    }
}