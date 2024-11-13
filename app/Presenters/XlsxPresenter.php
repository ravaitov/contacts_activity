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


    public function __construct(array $data)
    {
        parent::__construct($data);
//        $this->log(print_r($data, 1));

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $this->spreadSheet = $reader->load($this->config->conf('xlsx_tmpl'));
        $this->addStyle('red', 'A2');
        $this->addStyle('ok', 'A1');

        $this->sheet = $this->spreadSheet->getSheetByName('данные');
        $this->templateSheet = $this->spreadSheet->getSheetByName('tmpl');

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
        $y = 4;
        for ($i = 0; $i < $cnt; $i++) {
            $rowX = $this->getLeftFields($i);
            foreach ($this->data['data'][$i]['products'] as $product) {
                $row = $rowX;
                foreach ($product as $key => $el) {
                    if (in_array($key, ['login', 'fio4ois'])) // скрыть логины
                        continue;
                    $row[] = $el;
                }
                $c = 0;
                foreach ($row as $val) {
                    $this->sheet->setCellValue([++$c, $y], $val);
                }
                if ($product['ide_product'] == '-') {
                    $this->setStyleTemplate('red', [1, $y, $c, $y]);
                } else {
                    $this->setStyleTemplate('ok', [1, $y, $c, $y]);
                }
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
        $this->sheet->setCellValue([12, 1], 'недели');
        for ($i = 0; $i < $count; $i++) {
            $this->setWeek(12 + $i * 3, $weeks[$i]);
        }
        $this->sheet->mergeCells([12, 1, 11 + $count * 3, 1]);
        $this->sheet->setCellValue([12 + $count * 3, 1], 'Итог');
        $this->sheet->mergeCells([12 + $count * 3, 1, 12 + $count * 3, 3]);
        $this->setStyle('A1', [12, 1, 12 + $count * 3, 3]);
    }

    private function setWeek(int $x, string $week): void
    {
        $this->sheet->setCellValue([$x, 2], $week);
        $this->sheet->mergeCells([$x, 2, $x + 2, 2]);
        $this->sheet->setCellValue([$x, 3], 'Онлайн');
        $this->sheet->setCellValue([$x + 1, 3], 'Офлайн');
        $this->sheet->setCellValue([$x + 2, 3], 'Итог');
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