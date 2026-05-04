<?php

namespace App\Core;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

final class ExcelExport
{
    private array $nameConfig = [];
    private Spreadsheet $excelBase;
    private int $index = 0;

    public function __construct(
        private string $excelName,

    ) {
        $this->excelBase = new Spreadsheet();
        $this->excelBase->removeSheetByIndex(0);
    }

    public function addColumn(string $keyName, string $showName = ''): ExcelExport
    {
        $keyName = chr(63 + count($this->nameConfig));
        return $this;
    }

    public function addSheet(ExcelSheet $sheet): ExcelExport
    {
        $this->excelBase->addSheet($sheet->getSheet(), ++ $this->index);
        return $this;
    }

    public function exportFile(string $title): Response
    {
        $filename = time();
        $xlsx = new Xlsx($this->excelBase);
        $tmpFile = realpath(__DIR__ . '/../../var/') . time() . '.xlsx';
        $xlsx->save($tmpFile);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . mb_convert_encoding($title, 'UTF-8') . '.xlsx"');
        $response->setContent(file_get_contents($tmpFile));
        unlink($tmpFile);
        return $response;
    }
}
