<?php

namespace App\Presenters;

use App\AbstractApp;
use PhpOffice\PhpSpreadsheet\Calculation\Functions;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Validations;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class XlsxPresenter extends AbstractPresenter
{
    private string $fileName;
    private Spreadsheet $spreadSheet;
    private Worksheet $sheet;
    private Worksheet $templateSheet;
    private array $styles;

    private int $startWeeks = 13;

    public function __construct(array $data)
    {
        parent::__construct($data);
//        $this->log(print_r($data, 1));


        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $this->spreadSheet = $reader->load($this->config->conf('xlsx_tmpl'));
        $this->sheet = $this->spreadSheet->getSheetByName('данные');
        $this->templateSheet = $this->spreadSheet->getSheetByName('tmpl');

        $this->addStyle('red', 'A2');
        $this->addStyle('ok', 'A1');


        $this->fileName = 'report_' . AbstractApp::$params['week'] . '_' . AbstractApp::$params['date'] . '.xlsx';
    }

    public function sendTable(): void
    {
        $this->makeTitle($this->data['weeks']);
        $this->writeRows();

        $writer = new Xlsx($this->spreadSheet);
        $this->spreadSheet->removeSheetByIndex($this->spreadSheet->getIndex($this->templateSheet));

        $this->send($writer);
    }

    private function writeRows(): void
    {
        $cnt = count($this->data['data']);
        $y = 2;
        for ($i = 0; $i < $cnt; $i++) {
            $rowX = $this->getLeftFields($i);
            foreach ($this->data['data'][$i]['products'] as $product) {
                $row = $rowX;
                foreach ($product as $key => $el) {
                    if (in_array($key, ['login'])) // скрыть логины
                        continue;
                    $row[] = $key == 'fio4ois' ? ($el ? 'да' : 'нет') : $el;
                }
                $warn = $product['ide_product'] == '-';
                $c = 0;
                foreach ($row as $val) {
                    $this->sheet->setCellValue([++$c, $y], $val);
                    if ($c > 9 && $warn)
                        break;
                }
                $this->setStyleTemplate($warn ? 'red' : 'ok', [1, $y, $c, $y]);
                $y++;
            }
        }
    }

    private function getLeftFields(int $id): array
    {
        $out = [];
        foreach ($this->data['data'][$id] as $key => $el) {
            if ($key == 'products')
                return $out;
            $out[] = is_array($el) ? $el['name'] : $el;
        }
    }

    private function makeTitle(array $weeks): void
    {
        $count = count($weeks);
        for ($i = 0; $i < $count; $i++) {
            $this->setWeek($this->startWeeks + $i * 3, $weeks[$i]);
        }
        $x = $this->startWeeks + $count * 3;
        $this->sheet->setCellValue([$x, 1], 'Итог');
        $this->sheet->setCellValue([$x + 1, 1], "Итог\nпользователя");
        $this->setStyle('A1', [$this->startWeeks, 1, $x + 1, 1]);
    }

    private function setWeek(int $x, string $week): void
    {
        $this->sheet->setCellValue([$x, 1], $week);
        $this->sheet->setCellValue([$x, 1], "$week\nОнлайн");
        $this->sheet->setCellValue([$x + 1, 1], "$week\nОфлайн");
        $this->sheet->setCellValue([$x + 2, 1], "$week\nИтог");
    }

    private function array2str(array $x_y): string
    {
        return Functions::trimSheetFromCellReference(Validations::validateCellRange($x_y));
    }

    private function setStyle(string $from, array $to): void
    {
        $this->sheet->duplicateStyle($this->sheet->getStyle($from), $this->array2str($to));
    }

    private function addStyle(string $name, string $cell): void
    {
        $this->styles[$name] = $cell;
    }

    private function setStyleTemplate(string $name, array $to): void
    {
        $this->sheet->duplicateStyle($this->templateSheet->getStyle($this->styles[$name]), $this->array2str($to));
    }

    private function send(Xlsx $writer): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;fileName=\"$this->fileName\"");
        header('Cache-Control: max-age=0');
        header('Expires: Fri, 12 Nov 2012 12:11:22 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        $writer->save('php://output');
    }


}