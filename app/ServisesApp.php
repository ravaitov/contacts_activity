<?php

namespace App;

use PDO;

class ServisesApp extends AbstractApp
{
    public int $companyId;
    public string $startDate;
    public string $endDate;

    private string $cache;
    private array $week2Month;
    private bool $send = true;

    public function prepare(array $params = []): void
    {
        $this->log('params = ' . implode(', ', $params));
        $this->cache = $this->config->conf('stor_dir') . implode('_', array_slice($params, 0, 3));
        $this->companyId = $params[0];
        $this->startDate = $params[1] . '-01';
        $this->endDate = $this->lastDay($params[2] . "-01", ''); //2024-06-30
        $this->startDate2 = str_replace('-', '', $this->startDate); // 20230701
        $this->endDate2 = str_replace('-', '', $params[2]) . "01"; // 20240601
        $this->send = empty($params[3]);

    }

    public function run(): void
    {
        if (file_exists($this->cache)) {
            $this->result = unserialize(file_get_contents($this->cache));
        } else {
            $this->result['услуги'] = [
                'ЛК' => $this->getCountConsultLine(),
                'семинары' => $this->getCountSeminars(),
                'КПК' => $this->getCountUpCourses(),
                'Обученные пользователи' => $this->getTrainedUsers(),
            ];
            $this->result['системы'] = $this->getSystemsUsing();
            $this->result['компания'] = $this->getTitle();
            file_put_contents($this->cache, serialize($this->result));
        }
        $this->result['company_id'] = $this->companyId;
        $this->result['file_name'] = $this->companyId . '_' . $this->startDate . '_' . $this->endDate;
        $this->result['period'] = [explode('-', $this->startDate), explode('-', $this->endDate)];
        $this->result['send'] = $this->send;
//        $this->log(print_r($this->result, 1));
        $xlsx = new Report2Xlsx;
        $xlsx->prepare($this->result);
        $xlsx->run();
        $xlsx->finish();
    }

    private function getCountConsultLine(): array
    {
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
            	iep2.VALUE = $this->companyId
            	AND DATE_CREATE BETWEEN '$this->startDate'
            	AND '$this->endDate'
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

    private function getCountUpCourses(): array
    {
        $sql = <<<SQL
            SELECT
            	idCompany,
            	semOld.typeSem,
            	semOld.typeSemId,
            	COUNT(semOld.idSeminar) AS cnt
            FROM
            	(
            		SELECT
            			iep.IBLOCK_ELEMENT_ID,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1187 THEN iep.VALUE END
            			) AS idCompany,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1188 THEN iep.VALUE END
            			) AS seminar,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1436 THEN iep.VALUE END
            			) AS dateBeginCourse
            		FROM
            			b_iblock_element_property iep
            			LEFT JOIN b_iblock_element ie ON iep.IBLOCK_ELEMENT_ID = ie.ID
            		WHERE
            			ie.IBLOCK_ID = 128
            		GROUP BY
            			iep.IBLOCK_ELEMENT_ID
            	) AS semPos
            	LEFT JOIN (
            		SELECT
            			iep.IBLOCK_ELEMENT_ID AS idSeminar,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1411 THEN ienum.ID END
            			) AS typeSemId,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1411 THEN ienum.VALUE END
            			) AS typeSem,
            			DATE_FORMAT(
            				MAX(
            					CASE WHEN iep.IBLOCK_PROPERTY_ID = 743 THEN iep.VALUE END
            				),
            				'%Y-%m-%d'
            			) AS dateSem
            		FROM
            			b_iblock_element_property iep
            			LEFT JOIN b_iblock_element ie ON iep.IBLOCK_ELEMENT_ID = ie.ID
            			LEFT JOIN b_iblock_property_enum ienum ON iep.IBLOCK_PROPERTY_ID = ienum.PROPERTY_ID
            			AND ienum.ID = iep.VALUE
            		WHERE
            			ie.IBLOCK_ID = 86
            		GROUP BY
            			iep.IBLOCK_ELEMENT_ID
            	) AS semOld ON semPos.seminar = semOld.idSeminar
            WHERE
            	idCompany = $this->companyId
            	AND semOld.dateSem BETWEEN '$this->startDate'
            	AND '$this->endDate'
            GROUP BY
            	idCompany,
            	semOld.typeSem,
            	semOld.typeSemId
            HAVING
            	semOld.typeSemId IN (1896, 1908)			
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($el) => [$el['typeSem'], $el['cnt']], $res);
    }

    private function getCountSeminars(): array
    {
        $sql = <<<SQL
            SELECT
            	semPos.idCompany,
            	semOld.typeSem,
            	semOld.typeSemId,
            	COUNT(semOld.idSeminar) AS cnt,
            	semOld.paid
            FROM
            	(
            		SELECT
            			iep.IBLOCK_ELEMENT_ID,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1187 THEN iep.VALUE END
            			) AS idCompany,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1188 THEN iep.VALUE END
            			) AS seminar,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1436 THEN iep.VALUE END
            			) AS dateBeginCourse
            		FROM
            			b_iblock_element_property iep
            			LEFT JOIN b_iblock_element ie ON iep.IBLOCK_ELEMENT_ID = ie.ID
            		WHERE
            			ie.IBLOCK_ID = 128
            		GROUP BY
            			iep.IBLOCK_ELEMENT_ID
            	) AS semPos
            	LEFT JOIN (
            		SELECT
            			iep.IBLOCK_ELEMENT_ID AS idSeminar,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1411 THEN ienum.ID END
            			) AS typeSemId,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1411 THEN ienum.VALUE END
            			) AS typeSem,
            			MAX(
            				CASE WHEN iep.IBLOCK_PROPERTY_ID = 1446 THEN ienum.VALUE END
            			) AS paid,
            			DATE_FORMAT(
            				MAX(
            					CASE WHEN iep.IBLOCK_PROPERTY_ID = 743 THEN iep.VALUE END
            				),
            				'%Y-%m-%d'
            			) AS dateSem
            		FROM
            			b_iblock_element_property iep
            			LEFT JOIN b_iblock_element ie ON iep.IBLOCK_ELEMENT_ID = ie.ID
            			LEFT JOIN b_iblock_property_enum ienum ON iep.IBLOCK_PROPERTY_ID = ienum.PROPERTY_ID
            			AND ienum.ID = iep.VALUE
            		WHERE
            			ie.IBLOCK_ID = 86
            		GROUP BY
            			iep.IBLOCK_ELEMENT_ID
            	) AS semOld ON semPos.seminar = semOld.idSeminar
            WHERE
            	semPos.idCompany = $this->companyId
            	AND semOld.dateSem BETWEEN '$this->startDate'
            	AND '$this->endDate'
            GROUP BY
            	semPos.idCompany,
            	semOld.typeSem,
            	semOld.typeSemId,
            	semOld.paid
            HAVING
            	semOld.typeSemId NOT IN (1896, 1908)
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $freeCnt = 0;
        $out = [];
        foreach ($res as $row) {
            if ($row['paid'] != 'Платно') {
                $freeCnt += $row['cnt'];
                continue;
            }
            $out[] = [$row['typeSem'], $row['cnt']];
        }
        $out[] = ['Бесплатная линейка', $freeCnt];

        return $out;
    }

    private function getTrainedUsers(): array
    {
        $sql = <<<SQL
            SELECT
            	bccc.COMPANY_ID,
            	COUNT(DISTINCT iep.VALUE) AS cnt
            FROM
            	b_iblock_element ie
            	INNER JOIN b_iblock_element_property iep ON iep.IBLOCK_ELEMENT_ID = ie.ID
            	INNER JOIN b_crm_contact_company AS bccc ON bccc.CONTACT_ID = iep.VALUE
            WHERE
            	ie.IBLOCK_ID = 260
            	AND iep.IBLOCK_PROPERTY_ID = 2955
            	AND bccc.COMPANY_ID = $this->companyId
            GROUP BY
            	bccc.COMPANY_ID
        SQL;
        $res = $this->baseB24->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return [['', $res[0]['cnt']]];
    }

    private function getSystemsUsing(): array
    {
        $start = str_replace('-', '', $this->startDate);
        $sql = <<<SQL
            declare @date1 date = '$this->startDate2',
            @date2 date = '$this->endDate2' declare @cnt_week int
            SET
            	@cnt_week = DATEDIFF(
            		week,
            		@date1,
            		EOMONTH(@date2)
            	) SET NOCOUNT ON ; EXEC SP_Otchet_USR122_OV_OVM_T1 @CountWeeks = @cnt_week,
            	@sDOkon = @date2
            SELECT
            	o.Num_1,
            	t1.IdeProdukt,
            	stt.IdeTechType,
            	t1.IdeVer,
            	t1.NumWeek,
            	t1.NumYear,
            	COUNT(t1.IDDis) AS cnt
            FROM
            	##tmp_usr122_OV_OVM_T1 as t1
            	INNER JOIN RClient4.dbo.Org AS o ON o.NamOrg = t1.NamOrgTO COLLATE Cyrillic_General_CI_AS
            	LEFT JOIN RClient4.dbo.SprTechType AS stt ON stt.KodTechType = t1.KodTechType
            WHERE
            	Activity = '1'
            	AND o.Num_1 = $this->companyId
            GROUP BY
            	o.Num_1,
            	t1.IdeProdukt,
            	stt.IdeTechType,
            	t1.IdeVer,
            	t1.NumWeek,
            	t1.NumYear
            ORDER BY
            	t1.NumYear,
            	t1.NumWeek,
            	t1.IdeProdukt			
        SQL;
//        $cache = $this->cache . '_5';
//        if (file_exists($cache)) {
//            $res = unserialize(file_get_contents($cache));
//        } else {
//            $res = $this->baseMs->query($sql)->fetchAll(PDO::FETCH_ASSOC);
//            file_put_contents($cache, serialize($res));
//        }
        $res = $this->baseMs->query($sql)->fetchAll(PDO::FETCH_ASSOC);
//        $cache = $this->cache . '_5';
//        $res = unserialize(file_get_contents($cache));
//        $this->log(print_r($res, 1));

        $this->initWeek2Month();
//        print_r($this->week2Month);

        return $this->compactWeeks($this->compactSystems($res));
    }

    // мапим недели на месяцы
    private function initWeek2Month(): void
    {
        $week = 7 * 24 * 3600;
        $week05 = $week % 2;
        $start = strtotime($this->startDate) - 2 * $week; // диапазон расширяем на +- 2 недели
        $end = strtotime($this->endDate) + 2 * $week;
        for ($time = $start; $time <= $end; $time += $week) {
            $this->week2Month[date('Y-W', $time)] = date('Y-m', $time + $week05); //$week05 - сдвиг
        }
//        $this->log(print_r($this->week2Month, 1));
    }

    // собираем данные по системам
    private function compactSystems(array $rowData): array
    {
        foreach ($rowData as $el) {
            $prodKey = sprintf('%s|%s|%s', $el['IdeProdukt'], $el['IdeTechType'], $el['IdeVer']);
            $yearWeek = sprintf('%4s-%02s', $el['NumYear'], $el['NumWeek']);
            $compact[$prodKey][$yearWeek] = $el['cnt'];
        }
//        $this->log(print_r($compact, 1));
        return $compact;
    }

    // собираем недели в месяцы
    private function compactWeeks(array $weekData): array
    {
        $result = [];
        foreach ($weekData as $prod => $weeks) {
            foreach ($weeks as $week => $cnt) {
                $result[$prod][$this->week2Month[$week]] ??= 0;
                $result[$prod][$this->week2Month[$week]] += $cnt;
            }
        }
//        $this->log(print_r($result, 1));
        return $result;
    }

    private function getTitle(): string
    {
        $res = $this->baseMs->query('SELECT NamOrg FROM Org WHERE Num_1 = ' . $this->companyId);
        return $res->fetchAll()[0][0] ?? '';
    }
}