<?php

namespace App;

class ContactComplect234 extends AbstractApp
{
    public function run(): void
    {
        $sql = <<<SQL
            SELECT
            	NAME,
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2604 THEN iep.VALUE END) AS company_id,
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2606 THEN iep.VALUE END) AS contact_id,
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2611 THEN iep.VALUE END) AS ide_product, -- Сокращенное название               
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2609 THEN iep.VALUE END) AS complect,  -- Комплект
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2614 THEN iep.VALUE END) AS version,  -- Версия
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2615 THEN iep.VALUE END) AS net_type, -- Сетевитость
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2616 THEN iep.VALUE END) AS tech_type, -- Тех. тип
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2607 THEN iep.VALUE END) AS login, -- Логин (онлайн)
            	MAX(CASE WHEN iep.IBLOCK_PROPERTY_ID = 2898 THEN iep.VALUE END) AS fio4ois -- ФИО (для ОИС)
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
            $this->result[$el['company_id']][$el['contact_id']][] = $this->fillProduct($el);
        }
//        print_r($this->result);
    }

    public function fillProduct(array $product = []): array
    {
        return [
            'ide_product' => $product['ide_product'] ?? '-',
            'complect' => $product ? $product['complect'] : 'Нет привязки',
            'version' => $product['version'] ?? '',
//                'net_type' => $product['net_type'] ?? '',
            'tech_type' => $product['tech_type'] ?? '',
            'login' => $product['login'] ?? '',
            'fio4ois' => $product['fio4ois'] ?? '',
        ];
    }
}