<?php

namespace App;

use App\Logger\Logger;
use PDO;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


class Report2Xlsx extends AbstractApp
{
    private int $seminarId = 0;
    protected bool $send = false;

    private int $currentRow = 2;
    private string $endCol = 'M';
    private int $titleRow = 4;
    private string $statusField = 'UF_CRM_1524464429';
    private array $statusMap;
    private string $fileName;
    private array $styles;
    private Spreadsheet $spreadSheet;
    private Worksheet $sheet;
    private Worksheet $templateSheet;
    private array $companies;
    private array $users;

    public function __construct()
    {
        Logger::instance()->echoLog = false;
        parent::__construct('Экспорт в xslx');
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $this->spreadSheet = $reader->load($this->config->conf('tst_xlsx3'));

        $this->sheet = $this->spreadSheet->getSheetByName('info');
        $this->templateSheet = $this->spreadSheet->getSheetByName('tmpl');

        $this->addStyle('h1', 'A1');
        $this->addStyle('h2', 'A2');
        $this->addStyle('h4', 'A4');
        $this->addStyle('norm', 'A6');

//        $this->sheet->getStyle('A1:K40')->getAlignment()->setWrapText(true);

    }

    public function prepare(array $params = []): void
    {
        $this->seminarId = $params[0] ?? 0;
        $this->send = !empty($params['send']);
        $this->fileName = "sr$this->seminarId.xlsx";
        $this->log("seminarId=$this->seminarId, send=" . ($this->send ? 'true' : 'false'));
    }

    protected function protectRun(): void
    {
        $tableRegistry = $this->registerList('isnull(seminars_registrations_bil)');
        $tableVizited = $this->registerList('seminars_registrations_bil=1');
        $tableMissed = $this->registerList('seminars_registrations_bil=0');

        $this->getCompaniesByRest();
        $this->getUsersByRest();
        $this->getStatusFieldByRest();

        $this->writeHeader($this->currentRow++, 'Реестр семинара', 'h1');
        $this->writeHeader($this->currentRow++, $this->seminarName(), 'h2');
        $this->currentRow++;
        $this->fillTable($tableRegistry, 'Нет записей');
        $this->blankLine();

        $this->writeHeader($this->currentRow++, 'Посетили семинар', 'h1');
        $this->currentRow++;
        $this->fillTable($tableVizited, 'Пришедших нет');
        $this->blankLine();

        $this->writeHeader($this->currentRow++, 'Пропустили семинар', 'h1');
        $this->currentRow++;
        $this->fillTable($tableMissed, 'Пропустивших нет');

//        return;
//        $this->log($this->seminarName());
//        $this->log(print_r($this->registerList('isnull(seminars_registrations_bil)'), 1));
//        $this->log(print_r($this->registerList('seminars_registrations_bil=1'), 1));
//        $this->log(print_r($this->registerList('seminars_registrations_bil=0'), 1));
    }

    protected function finish(): void
    {
//        $this->sheet->getStyle('A1:K'.$this->currentRow)->getAlignment()->setWrapText(true);
//        foreach($this->sheet->getRowDimensions() as $rowID) {
//            $rowID->setRowHeight(-1);
//        }
        $this->sheet->getDefaultRowDimension()->setRowHeight(-1);

        $this->spreadSheet->removeSheetByIndex($this->spreadSheet->getIndex($this->templateSheet));
        $this->sheet->setSelectedCell('A2');
        $writer = new Xlsx($this->spreadSheet);
        if ($this->send) {
            $this->sendTable($writer);
        } else {
            $writer->save($this->config->conf('storage') . $this->fileName);
        }
    }

    private function sendTable(Xlsx $writer): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->fileName . '"');
        header('Cache-Control: max-age=0');
        header('Expires: Fri, 12 Nov 2012 12:11:22 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        $writer->save('php://output');
    }

    private function seminarName(): string
    {
        return
            html_entity_decode($this->base->queryObject(
                "select seminars_name name from seminars where seminars_id = $this->seminarId"
            )->name);
    }

    private function fillTable(array $row, string $zeroTitle = ''): bool
    {
        if (!$row) {
            $this->writeHeader($this->currentRow - 1, $zeroTitle, 'h2');
            return false;
        }
        $table = $this->presentData($row);
        $count = count($table);
        $this->writeTitleRow();
        $this->sheet->fromArray($table, NULL, 'A' . $this->currentRow);
        $this->setStyle('norm', sprintf('A%s:%s%s', $this->currentRow, $this->endCol, $this->currentRow + $count - 1));
        $this->currentRow += $count;

        return true;
    }

    private function presentData(array $row): array
    {
        $res = [];
        foreach ($row as $item) {
            $row = [
                $item['company'] ?? '',
                $item['c_name'] ?? '',
                $this->statusMap[$this->companies[$item['company_id']]] ?? 'не установлено',
                $item['bilet'] ?? '',
                $item['bilet_summ'] ?? '',
                $item['phone'] ?? '',
                empty($item['bilet_sert_email']) ? ($item['email'] ?? '') : $item['bilet_sert_email'],
                $this->users[$item['company_assigned_id']] ?? '',
                $item['comment'] ?? '',
                $this->users[$item['register_user']] ?? '',
                $item['register_time'] ?? '',
                empty($item['dogovor']) ? 'Нет' : 'Да',
                $item['dogovor_comment'] ?? '',
            ];
            $res[] = array_map(fn(string $el): string => trim($el), $row);
        }
        return $res;
    }

    private function addStyle(string $name, string $cell): void
    {
        $this->styles[$name] = $cell;
    }

    private function setStyle(string $name, string $region): void
    {
        $this->sheet->duplicateStyle($this->templateSheet->getStyle($this->styles[$name]), $region);
    }

    private function writeWithStyle(string $text, string $range, string $style): void
    {
        $this->sheet->setCellValue($range, $text);
        $this->setStyle($style, $range);
    }

    private function writeHeader(int $row, string $header, string $style): void
    {
        $this->writeWithStyle($header, "A$row", $style);
        $this->sheet->mergeCells("A$row:$this->endCol$row");
    }

    private function blankLine(): void
    {
        $this->writeHeader($this->currentRow++, '', 'h1');
    }

    private function writeTitleRow(): void
    {
        $titleRow = [];
        for ($col = 'A'; $col <= $this->endCol; $col++) {
            $titleRow[] = $this->templateSheet->getCell("$col$this->titleRow")->getValue();
        }
        $this->sheet->fromArray($titleRow, NULL, 'A' . $this->currentRow);
        $this->setStyle('h4', sprintf('A%s:%s%s', $this->currentRow, $this->endCol, $this->currentRow++));
    }

    /**
     * @param string $vizitedCondition
     * {isnull(seminars_registrations_bil)|seminars_registrations_bil=0|seminars_registrations_bil=1 }
     */
    private function registerList(string $vizitedCondition): array
    {
        $res = $this->base->handle()->query(<<<SQL
            select 
                sr.seminars_id,
                sr.seminars_registrations_id sr_id,
                sr.bitrix_contactId_ID contact_id,     
                concat(cont_info.bitrix_contact_info_LAST_NAME, ' ', cont_info.bitrix_contact_info_Name, ' ', ifnull(cont_info.bitrix_contact_info_SECOND_NAME, '')) c_name,
                comp_id.bitrix_companyId_bitrixId company_id,
                comp_info.bitrix_company_info_TITLE company,
                comp_info.bitrix_company_info_ASSIGNED_BY_ID company_assigned_id,
                concat(bu_2.bitrix_users_LAST_NAME, ' ', bu_2.bitrix_users_NAME) responsible,
                sr.seminars_registrations_bilet bilet,
                sr.bilet_summ,
                sr.seminars_registrations_dogovor dogovor,
                sr.seminars_registrations_dogovorComment dogovor_comment,
                sr.seminars_registrations_comment comment,
                sr.seminars_registrations_whoregisters register_user,
                sr.seminars_registrations_whenregistered register_time,
                sr.seminars_registrations_done_who registrations_done_user,
                sr.seminars_registrations_done_when registrations_done_time,
                sr.phone,
                sr.`e-mail` email,
                sr.bilet_sert_email,
                sr.seminars_registrations_whoregisters whoregisters_id,
                concat(bu_1.bitrix_users_LAST_NAME, ' ', bu_1.bitrix_users_NAME) registration_name,
                cont_id.max_bitrix_contact_info_ID mail_phone
            from seminars_registrations sr
            left join bitrix_users bu_1 on bu_1.bitrix_users_bitrixID = sr.seminars_registrations_whoregisters

            left join bitrix_companyId comp_id on comp_id.bitrix_companyId_ID = sr.bitrix_companyId_ID
            left join bitrix_company_info comp_info on comp_info.bitrix_company_info_ID = comp_id.max_bitrix_company_info_ID
            left join bitrix_users bu_2 on bu_2.bitrix_users_bitrixID = comp_info.bitrix_company_info_ASSIGNED_BY_ID

            left join bitrix_contactId cont_id on cont_id.bitrix_contactId_ID = sr.bitrix_contactId_ID
            left join bitrix_contact_info cont_info on cont_info.bitrix_contact_info_ID = cont_id.max_bitrix_contact_info_ID

            where seminars_id = $this->seminarId 
            and $vizitedCondition
        SQL
        )->fetchAll(PDO::FETCH_ASSOC);

        $whereIn = '';
        foreach ($res as $row) {
            $whereIn .= $row['mail_phone'] ? ($row['mail_phone'] . ',') : '';
            $this->companies[] = $row['company_id'];
            $this->users[] = $row['register_user'];
            $this->users[] = $row['company_assigned_id'];
            $this->users[] = $row['whoregisters_id'];
        }
        $whereIn = substr($whereIn, 0, -1);

        $mail_phone = $whereIn ?
            $this->base->handle()->query(<<<SQL
                select 
                    bitrix_contact_info_ID id,
                    bitrix_contact_info_contacts_type type,
                    bitrix_contact_info_contacts_value_type scope,
                    bitrix_contact_info_contacts_value val
                from bitrix_contact_info_contacts
                where bitrix_contact_info_ID in ($whereIn)
            SQL
            )->fetchAll(PDO::FETCH_ASSOC) : [];

        $compact = [];
        foreach ($mail_phone as $item) {
            $compact[$item['id']][$item['type']][$item['scope']][] = $item['val'];
        }

        foreach ($res as &$item) {
            $lines = $compact[$item['mail_phone']] ?? [];
            foreach (['PHONE', 'EMAIL'] as $type) {
                if (empty($lines[$type]))
                    continue;
                $str = '';

                foreach ($lines[$type] as $el) {
                    $str .= $el ? (" " . implode(', ', $el) . ' ') : '';
                }
                $item[strtolower($type)] = $str;
            }
        }
//        $this->log(print_r($res, 1));

        return $res;
    }

    private function getStatusFieldByRest(): void
    {
        $res = $this->restWH->call(
            'crm.company.userfield.list',
            ['filter' => ['FIELD_NAME' => $this->statusField]]
        );
        foreach ($res[0]['LIST'] as $el) {
            $this->statusMap[$el['ID']] = $el['VALUE'];
        }
    }

    private function getCompaniesByRest(): void
    {
        $companies = array_unique($this->companies);
        $this->companies = [];
        foreach (array_chunk($companies, 50) as $chunk) {
            $res = $this->restWH->call(
                'crm.company.list',
                [
                    'select' => ['ID',$this->statusField],
                    'filter' => ['ID' => $chunk]
                ]
            );
            foreach ($res as $row) {
                $this->companies[$row['ID']] = $row[$this->statusField];
            }
        }
    }

    private function getUsersByRest(): void
    {
        $users = array_unique($this->users);
        $this->users = [];
        foreach (array_chunk($users, 50) as $chunk) {
            $res = $this->restWH->call(
                'user.search',
                [
                    'filter' => ['ID' => $chunk]
                ]
            );
            foreach ($res as $row) {
                $this->users[$row['ID']] = $row['LAST_NAME'] . ' ' . $row['NAME'];
            }
        }
    }
}