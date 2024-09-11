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

    private int $currentRow = 1;
    private string $endCol = 'M';
    private int $titleRow = 4;
    private array $statusMap;
    private string $fileName;
    private array $styles;
    private Spreadsheet $spreadSheet;
    private Worksheet $sheet;
    private Worksheet $templateSheet;
    private array $params;

    public function __construct()
    {
        parent::__construct();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $this->spreadSheet = $reader->load($this->config->conf('xlsx_tmpl'));

        $this->sheet = $this->spreadSheet->getSheetByName('данные');
        $this->templateSheet = $this->spreadSheet->getSheetByName('tmpl');

        $this->addStyle('c_bold', 'A1');
        $this->addStyle('l_bold', 'A7');
        $this->addStyle('c_norm', 'A10');
        $this->addStyle('l_norm', 'B3');

//        $this->sheet->getStyle('A1:K40')->getAlignment()->setWrapText(true);

    }

    public function prepare(array $params = []): void
    {
        $this->params = $params;
        $this->send = ($params['send']);
        $this->fileName = 'report_' . $params['file_name'] . '.xlsx';
        $this->log($this->fileName);
    }

    public function run(): void
    {
        $this->fillSystemsByMonth();
        $this->fillServicesUsage();
    }

    public function finish(): void
    {
        $this->sheet->getDefaultRowDimension()->setRowHeight(-1);

        $this->spreadSheet->removeSheetByIndex($this->spreadSheet->getIndex($this->templateSheet));
        $this->sheet->setSelectedCell('A2');
        $writer = new Xlsx($this->spreadSheet);
        if ($this->send) {
            $this->sendTable($writer);
        } else {
            $writer->save($this->config->conf('stor_dir') . $this->fileName);
        }
    }

    private function fillSystemsByMonth()
    {
        $monthName = ['', 'янв', 'фев', 'март', 'апр', 'май', 'июнь', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'];
        $startYear = $this->params['period'][0][0];
        $startMonth = $this->params['period'][0][1];
        $endtYear = $this->params['period'][1][0];
        $endtMonth = $this->params['period'][1][1];

        // заголовки
        $this->writeWithStyle('Данные по использованию системы', 'A1', 'c_bold');
        $this->sheet->mergeCells('A1:B1');
        $this->writeWithStyle($this->params['компания'], 'D1', 'c_bold');
        $this->sheet->mergeCells('D1:L1');
        $this->writeWithStyle('зафиксировать количество входов', 'A2', 'l_bold');
        $this->sheet->mergeCells('A2:C2');
        $this->writeWithStyle('период', 'D2', 'c_bold');

        $x = 'A';
        $y = 3;
        $this->writeWithStyle('Основная система', ($x++) . $y, 'c_norm');
        $this->writeWithStyle('Тех.тип', ($x++) . $y, 'c_norm');
        $this->writeWithStyle('Сетевитость', ($x++) . $y, 'c_norm');

        // месяцы
        $z = '';
        $startMonthTmp = $startMonth;
        for ($year = $startYear; $year <= $endtYear; $year++) {
            $suffix = '.' . substr($year, 2);
            for ($month = $startMonthTmp; $month <= ($year == $endtYear ? $endtMonth : 12); $month++) {
                $this->writeWithStyle($monthName[(int)$month] . $suffix, ($x++) . $y, 'c_norm');
                $suffix = '';
                if ($x == 'Z') {
                    $z = 'Z';
                }
            }
            $startMonthTmp = 1;
        }
        $this->sheet->mergeCells('D2:' . ($z ?: chr(ord($x) - 1)) . '2');

        foreach ($this->params['системы'] as $sys => $counts) {
            $x = 'A';
            $y++;
            foreach (explode('|', $sys) as $name) {
                $this->writeWithStyle($name, ($x++) . $y, 'c_norm');
            }
            // месяцы
            $startMonthTmp = $startMonth;
            for ($year = $startYear; $year <= $endtYear; $year++) {
                for ($month = $startMonthTmp; $month <= ($year == $endtYear ? $endtMonth : 12); $month++) {
                    $cnt = $counts[sprintf('%4s-%02s', $year, $month)] ?? 0;
                    $this->writeWithStyle($cnt, ($x++) . $y, 'c_norm');
                }
                $startMonthTmp = 1;
            }
        }
        $this->currentRow = $y;
    }

    private function fillServicesUsage(): void
    {
        $y = $this->currentRow + 2;
        $this->writeWithStyle('Данные по использованию сервисных услуг', "A$y", 'c_bold');
        $this->sheet->mergeCells("A$y:B$y");
        $this->sheet->getRowDimension($y)->setRowHeight(50, 'pt');
        $y += 2;
        $this->sheet->getRowDimension($y)->setRowHeight(30, 'pt');
        $this->sheet->mergeCells("A$y:B$y");
        $this->writeWithStyle('вид сервиса', "A$y", 'c_norm');
        $this->writeWithStyle('', "B$y", 'c_norm');
        $this->writeWithStyle('кол-во обращений', "C$y", 'l_norm');

        $y++;
        foreach ($this->params['услуги'] as $type => $els) {
            if (count($els) > 1) {
                $this->sheet->mergeCells("A$y:A" . ($y + count($els) - 1));
            }
            $this->writeWithStyle($type, "A$y", 'c_norm');
            foreach ($els as $el) {
                $this->writeWithStyle($el[0] ?: '', "B$y", 'l_norm');
                $this->writeWithStyle($el[1], "C$y", 'c_norm');
                $y++;
            }
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

}