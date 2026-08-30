<?php

namespace App\Services;

use App\Models\AccountingTransaction;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TransactionReportExporter
{
    public function export(array $filters): string
    {
        $transactions = AccountingTransaction::filtered($filters)->with('items')->orderBy('occurred_at')->get();
        $incomeLabels = $transactions->where('type', 'income')->flatMap->items->pluck('label')->unique()->sort()->values();
        $expenseLabels = $transactions->where('type', 'expense')->flatMap->items->pluck('label')->unique()->sort()->values();
        $months = $transactions->groupBy(fn ($transaction) => $transaction->occurred_at->format('Y-m'));

        if ($months->isEmpty()) {
            $months = collect([now()->format('Y-m') => collect()]);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income & Expenditure');
        $headers = collect(['MONTH'])->concat($incomeLabels)->concat(['Total Income', 'Total Expenses'])
            ->concat($expenseLabels)->values();
        $incomeStart = 2;
        $incomeEnd = $incomeStart + $incomeLabels->count() - 1;
        $totalIncomeColumn = $incomeEnd + 1;
        $totalExpenseColumn = $totalIncomeColumn + 1;
        $expenseStart = $totalExpenseColumn + 1;
        $lastColumn = max($expenseStart + $expenseLabels->count() - 1, $totalExpenseColumn);

        $sheet->setCellValue('A1', 'Income & Expenditure');
        $sheet->mergeCells('A1:' . Coordinate::stringFromColumnIndex($lastColumn) . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($totalExpenseColumn) . '3', '(Sum of expense columns)');

        foreach ($headers as $index => $header) {
            $column = $index + 1;
            $sheet->setCellValue([$column, 4], $header);
            $sheet->setCellValue([$column, 5], $column === 1 ? '' : '€');
        }

        $row = 6;
        foreach ($months as $month => $monthTransactions) {
            $sheet->setCellValue([1, $row], \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01')
            ));

            foreach ($incomeLabels as $index => $label) {
                $value = $monthTransactions->where('type', 'income')->flatMap->items
                    ->where('label', $label)->sum('total');
                $sheet->setCellValue([$incomeStart + $index, $row], $value);
            }
            foreach ($expenseLabels as $index => $label) {
                $value = $monthTransactions->where('type', 'expense')->flatMap->items
                    ->where('label', $label)->sum('total');
                $sheet->setCellValue([$expenseStart + $index, $row], $value);
            }

            $sheet->setCellValue([$totalIncomeColumn, $row], $monthTransactions->where('type', 'income')->sum('total'));
            $sheet->setCellValue([$totalExpenseColumn, $row], $monthTransactions->where('type', 'expense')->sum('total'));
            $row++;
        }

        $totalRow = $row;
        $sheet->setCellValue([1, $totalRow], 'TOTALS TO DATE');
        for ($column = 2; $column <= $lastColumn; $column++) {
            $letter = Coordinate::stringFromColumnIndex($column);
            $sheet->setCellValue([$column, $totalRow], "=SUM({$letter}6:{$letter}" . ($totalRow - 1) . ')');
        }

        $lastLetter = Coordinate::stringFromColumnIndex($lastColumn);
        $sheet->getStyle("A4:{$lastLetter}{$totalRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF7F7F7F'));
        $sheet->getStyle("A4:{$lastLetter}4")->getFill()->setFillType('solid')->getStartColor()->setRGB('F4B183');
        $sheet->getStyle("A5:A{$totalRow}")->getFill()->setFillType('solid')->getStartColor()->setRGB('FF0000');
        if ($incomeLabels->isNotEmpty()) {
            $incomeStartLetter = Coordinate::stringFromColumnIndex($incomeStart);
            $incomeEndLetter = Coordinate::stringFromColumnIndex($incomeEnd);
            $sheet->getStyle("{$incomeStartLetter}5:{$incomeEndLetter}{$totalRow}")->getFill()
                ->setFillType('solid')->getStartColor()->setRGB('8FD5EE');
        }
        $totalsStart = Coordinate::stringFromColumnIndex($totalIncomeColumn);
        $totalsEnd = Coordinate::stringFromColumnIndex($totalExpenseColumn);
        $sheet->getStyle("{$totalsStart}5:{$totalsEnd}{$totalRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('FFF200');
        if ($expenseLabels->isNotEmpty()) {
            $expenseStartLetter = Coordinate::stringFromColumnIndex($expenseStart);
            $sheet->getStyle("{$expenseStartLetter}5:{$lastLetter}{$totalRow}")->getFill()
                ->setFillType('solid')->getStartColor()->setRGB('D996D9');
        }
        $sheet->getStyle("A{$totalRow}:{$lastLetter}{$totalRow}")->getFill()
            ->setFillType('solid')->getStartColor()->setRGB('A9D18E');
        $sheet->getStyle("A4:{$lastLetter}5")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}:{$lastLetter}{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A6:A" . ($totalRow - 1))->getNumberFormat()->setFormatCode('mmm-yy');
        $sheet->getStyle("B6:{$lastLetter}{$totalRow}")->getNumberFormat()
            ->setFormatCode('€#,##0.00;[Red](€#,##0.00);-');
        $sheet->getStyle("A4:{$lastLetter}{$totalRow}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
        $sheet->freezePane('A6');
        $sheet->setAutoFilter("A4:{$lastLetter}4");
        $sheet->getColumnDimension('A')->setWidth(16);
        for ($column = 2; $column <= $lastColumn; $column++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setWidth(18);
        }
        $sheet->getRowDimension(4)->setRowHeight(42);

        $path = tempnam(sys_get_temp_dir(), 'income-expenditure-');
        if ($path === false) {
            throw new \RuntimeException('Unable to create a temporary file for the Excel export.');
        }

        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }
}
