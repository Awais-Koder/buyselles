<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Minimal single-product code upload template.
 *
 * Columns:
 *   A  pin           – the digital code / gift-card PIN  (REQUIRED)
 *   B  serial_number – optional reference number
 *   C  expiry_date   – optional, format YYYY-MM-DD
 */
class ProductCodeTemplateExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    use Exportable;

    public function __construct(private readonly string $productName = '') {}

    public function collection(): \Illuminate\Support\Collection
    {
        return collect([
            // Example row (green) — automatically skipped by the importer
            [
                'pin' => 'ABCD-1234-EFGH-5678',
                'serial_number' => 'SN-00123',
                'expiry_date' => '2026-12-31',
            ],
            // Blank rows for the user to fill in
            ['pin' => '', 'serial_number' => '', 'expiry_date' => ''],
            ['pin' => '', 'serial_number' => '', 'expiry_date' => ''],
            ['pin' => '', 'serial_number' => '', 'expiry_date' => ''],
        ]);
    }

    public function headings(): array
    {
        return ['pin', 'serial_number', 'expiry_date'];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row
        $sheet->getStyle('A1:C1')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A1:C1')->getFill()->applyFromArray([
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => '063C93'],
        ]);

        return [
            'A1:C1' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet;
                $lastRow = $sheet->getHighestRow();

                // Borders on all cells
                $sheet->getStyle('A1:C' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'CCCCCC'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Row 2 = example row — green + italic
                $sheet->getStyle('A2:C2')->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D4EDDA'],
                ]);
                $sheet->getStyle('A2:C2')->getFont()->setItalic(true);
                $sheet->getStyle('A2:C2')->getFont()->getColor()->setARGB('006400');

                // Highlight PIN column for data rows
                if ($lastRow > 2) {
                    $sheet->getStyle('A3:A' . $lastRow)->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFF3CD'],
                    ]);
                }

                // expiry_date header comment
                $comment = $sheet->getComment('C1');
                $comment->getText()->createTextRun("Date format: YYYY-MM-DD\nExample: 2026-12-31\nLeave blank for codes that do not expire.");
                $comment->setWidth('200pt')->setHeight('55pt');

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(36);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Product name note in merged row above header if provided
                if ($this->productName !== '') {
                    $sheet->insertNewRowBefore(1);
                    $sheet->mergeCells('A1:C1');
                    $sheet->setCellValue('A1', 'Product: ' . $this->productName . ' — Fill in one code per row. Delete the green example row before uploading.');
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['rgb' => '063C93']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'EBF3FF']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension(1)->setRowHeight(20);
                }
            },
        ];
    }
}
