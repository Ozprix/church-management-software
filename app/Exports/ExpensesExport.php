<?php

namespace App\Exports;

use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $startDate;
    protected $endDate;

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     */
    public function __construct(Carbon $startDate, Carbon $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Expense::with(['category', 'approver'])
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->orderBy('expense_date', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Date',
            'Category',
            'Description',
            'Amount',
            'Approved By',
            'Receipt URL'
        ];
    }

    /**
     * @param Expense $expense
     * @return array
     */
    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->expense_date->format('Y-m-d'),
            $expense->category ? $expense->category->name : 'N/A',
            $expense->description,
            $expense->amount,
            $expense->approver ? $expense->approver->name : 'N/A',
            $expense->receipt_url
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Expenses';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
            
            // Style the amount column as currency
            'E' => ['numberFormat' => ['formatCode' => '"$"#,##0.00_-']],
        ];
    }
}
