<?php

namespace App\Core;

use App\Core\ExcelData;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ExcelSheet
{
    private array $columns = [];
    private Worksheet $sheet;

    public function __construct(
        private string $title = "",
    ) {
        $this->sheet = new Worksheet(title: $this->title);
    }

    public function setSheet(Worksheet $sheet): ExcelSheet
    {
        $this->sheet = $sheet;
        return $this;
    }

    public function getSheet(): Worksheet
    {
        return $this->sheet;
    }

    public function addColumn(string $dataIndex, string $title, array $options = []): ExcelSheet
    {
        $key = chr(65 + count($this->columns));
        $this->columns[$key] = $dataIndex;
        $this->sheet->setCellValue($key. '1', $title);
        $colunm = $this->sheet->getColumnDimension($key);
        $colunm->setAutoSize(true);

        if (isset($options['width'])) {
            $colunm->setWidth($options['width']);
        }

        if (isset($options['collapsed'])) {
            $colunm->setCollapsed($options['collapsed']);
        }

        return $this;
    }

    public function addData(array $data, array $configData)
    {
        foreach ($data as $key => $value) {
            $excelData = new ExcelData($value);
            foreach ($this->columns as $keyName => $dataIndex) {
                $cellData = $excelData->getData($dataIndex);
                if (isset($configData[$dataIndex])) {
                    $cellData = $configData[$dataIndex][$cellData] ?? $cellData;
                }
                $this->sheet->setCellValue($keyName . ($key + 2), $cellData);
            }
        }
    }

}
