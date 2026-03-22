<?php

namespace App\Exports;

use App\Models\Product;
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

class DigitalProductCodeTemplateExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithStyles
{
    use Exportable;

    /**
     * Column layout:
     *   A  product_id       – leave blank to look up by name or create a new product
     *   B  product_name     – required; used for lookup or creation
     *   C  price            – required only when creating a NEW product
     *   D  category_id      – required only when creating a NEW product
     *   E  pin              – the digital code (REQUIRED)
     *   F  serial_number    – optional reference number
     *   G  expiry_date      – optional, format YYYY-MM-DD
     */
    public function __construct(protected readonly ?int $sellerId = null) {}

    public function collection(): \Illuminate\Support\Collection
    {
        $query = Product::query()
            ->where('product_type', 'digital')
            ->select('id', 'name', 'unit_price', 'category_id');

        if ($this->sellerId !== null) {
            $query->where('user_id', $this->sellerId)->where('added_by', 'seller');
        } else {
            $query->where('added_by', 'admin');
        }

        $rows = $query->get()->map(fn(Product $product): array => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => '',          // left blank — product already exists
            'category_id' => '',    // left blank — product already exists
            'pin' => '',            // fill this in
            'serial_number' => '',  // optional
            'expiry_date' => '',    // optional (YYYY-MM-DD e.g. 2026-12-31)
        ]);

        // Append blank rows for adding new products in the same upload
        for ($i = 0; $i < 5; $i++) {
            $rows->push([
                'product_id' => '',
                'product_name' => '',
                'price' => '',
                'category_id' => '',
                'pin' => '',
                'serial_number' => '',
                'expiry_date' => '',
            ]);
        }

        // Prepend a clearly-marked example row so users understand every column.
        // The importer skips any row whose product_name contains "⚠ EXAMPLE".
        $rows->prepend([
            'product_id' => '101',
            'product_name' => '⚠ EXAMPLE — DELETE THIS ROW',
            'price' => '9.99',
            'category_id' => '25',
            'pin' => 'ABCD-1234-EFGH-5678',
            'serial_number' => 'SN-00123',
            'expiry_date' => '2026-12-31',
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'product_id',
            'product_name',
            'price',
            'category_id',
            'pin',
            'serial_number',
            'expiry_date',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row — dark-blue background, white text
        $sheet->getStyle('A1:G1')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A1:G1')->getFill()->applyFromArray([
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => '063C93'],
        ]);

        return [
            'A1:G1' => [
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
                $lastRange = 'A1:G' . $lastRow;

                $sheet->getStyle($lastRange)->applyFromArray([
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

                if ($lastRow > 1) {
                    // Row 2 is always the example row — style it distinctively
                    $sheet->getStyle('A2:G2')->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'D4EDDA'], // light green
                    ]);
                    $sheet->getStyle('A2:G2')->getFont()->setItalic(true);
                    $sheet->getStyle('A2:G2')->getFont()->getColor()->setARGB('006400'); // dark green text

                    // Highlight PIN column (E) for all data rows — the most important field
                    if ($lastRow > 2) {
                        $sheet->getStyle('E3:E' . $lastRow)->getFill()->applyFromArray([
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => 'FFF3CD'],
                        ]);
                    }

                    // Highlight new-product-only columns (C price, D category_id) lightly
                    if ($lastRow > 2) {
                        foreach (['C', 'D'] as $col) {
                            $sheet->getStyle($col . '3:' . $col . $lastRow)->getFill()->applyFromArray([
                                'fillType' => Fill::FILL_SOLID,
                                'color' => ['rgb' => 'EAF6FF'],
                            ]);
                        }
                    }

                    // Add a comment on the expiry_date header (G1) explaining the format
                    $comment = $sheet->getComment('G1');
                    $comment->getText()->createTextRun("Date format: YYYY-MM-DD\nExample: 2026-12-31\nLeave blank for codes that do not expire.\nCodes past this date are marked expired automatically each night.");
                    $comment->setWidth('220pt');
                    $comment->setHeight('70pt');
                }

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(14);
                $sheet->getColumnDimension('B')->setWidth(42);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(16);
                $sheet->getColumnDimension('E')->setWidth(40);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(20);

                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}
