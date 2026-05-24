<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function inspectExcel($filePath) {
    echo "=========================================\n";
    echo "Inspecting: $filePath\n";
    echo "=========================================\n";
    if (!file_exists($filePath)) {
        echo "File does not exist.\n\n";
        return;
    }
    
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();
    
    if (empty($rows)) {
        echo "Empty sheet.\n\n";
        return;
    }
    
    $header = $rows[0];
    echo "Headers:\n";
    foreach ($header as $idx => $val) {
        echo "  [$idx] => " . ($val ?? '[NULL/EMPTY]') . "\n";
    }
    
    echo "\nRow 1:\n";
    if (isset($rows[1])) {
        foreach ($rows[1] as $idx => $val) {
            echo "  [$idx] => " . ($val ?? '[NULL/EMPTY]') . "\n";
        }
    }
    echo "\n";
}

inspectExcel("D:/joki hanif/sistem-kmeans/public/data_cleaned_.xlsx");
inspectExcel("D:/joki hanif/sistem-kmeans/public/PERHITUNGAN KMEAN.xlsx");
