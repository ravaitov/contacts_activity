<?php

namespace App;

use PDO;

class TestApp extends AbstractApp
{
    public function prepare(array $params = []): void
    {

    }

    public function run(): void
    {
        $this->log(print_r($this->getCountConsultLine(1055, '2023-07', '2024-06')));
    }

    private function getCountConsultLine(int $companyId, string $start, string $end): array
    {
        $start = "$start-1";
        $end = $this->lastDay("$end-1");
        $sql = <<<SQL
            SELECT
            	ienum.VALUE AS 'type',
            	ienum.ID,
            	iep2.VALUE AS company_id,
            	COUNT(iep.IBLOCK_ELEMENT_ID) AS cnt
            FROM
            	b_iblock_element ie
            	INNER JOIN b_iblock_element_property iep ON iep.IBLOCK_ELEMENT_ID = ie.ID
            	LEFT JOIN b_iblock_property_enum ienum ON iep.IBLOCK_PROPERTY_ID = ienum.PROPERTY_ID
            	AND ienum.ID = iep.VALUE
            	LEFT JOIN b_iblock_element_property iep2 ON iep2.IBLOCK_ELEMENT_ID = ie.ID
            	AND iep2.IBLOCK_PROPERTY_ID = 832
            WHERE
            	iep2.VALUE = $companyId
            	AND DATE_CREATE BETWEEN '$start'
            	AND '$end'
            	AND iep.IBLOCK_PROPERTY_ID = 837
            GROUP BY
            	ienum.VALUE,
            	ienum.ID
            HAVING
            	ienum.ID <> 1298
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($el) => [$el['type'], $el['cnt']], $res);
    }
}