<?php

namespace App;

class ContactComplect extends AbstractApp
{
    public array $contactName;

    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	NAME,
            	MAX(
            		CASE WHEN iep.IBLOCK_PROPERTY_ID = 2604 THEN iep.VALUE END
            	) AS company_id,
            	MAX(
            		CASE WHEN iep.IBLOCK_PROPERTY_ID = 2606 THEN iep.VALUE END
            	) AS contact_id,
            	MAX(
            		CASE WHEN iep.IBLOCK_PROPERTY_ID = 2611 THEN iep.VALUE END
            	) AS ide_product,
            	MAX(
            		CASE WHEN iep.IBLOCK_PROPERTY_ID = 2609 THEN iep.VALUE END
            	) AS complect,
            	MAX(
            		CASE WHEN iep.IBLOCK_PROPERTY_ID = 2614 THEN iep.VALUE END
            	) AS net_type
            FROM
            	b_iblock_element ie
            	INNER JOIN b_iblock_element_property iep ON iep.IBLOCK_ELEMENT_ID = ie.ID
            WHERE
            	ie.IBLOCK_ID = 234
            GROUP BY
            	iep.IBLOCK_ELEMENT_ID			
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($res as $el) {
            if (empty($el['company_id']) || empty($el['contact_id']))
                continue;
            $this->result[$el['company_id']][$el['contact_id']] = [$el['ide_product'], $el['complect'], $el['net_type']];
            $this->contactName[$el['contact_id']] = $el['NAME'];
        }
//        print_r($this->result);
    }

    public function contactName(int $id): string
    {
        return $this->contactName[$id] ?? '';
    }
}