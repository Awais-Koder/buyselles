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

    protected ?int $sellerId;

    public function __construct(?int $sellerId = null)
    {
        $this->sellerId = $sellerId;
    }

    public function collection(): \Illuminate\Support\Collection
    {
        $query = Product::query()
            ->where('product_type', 'digital')
            ->selectRaw('id, name, digital_product_type, (SELECT COUNT(*) FROM digital_product_codes WHERE product_id = products.id AND status = "available") as available_codes_count');

        if ($this->sellerId !== null) {
            $query->where('user_id', $this->sellerId)->where('added_by', 'seller');
        } else {
            $query->where('added_by', 'admin');
        }

        return $query->get()->map(function (Product $product): array {
            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'digital_product_type' => $product->digital_product_type ?? 'ready_product',
                'has_code_already' => $product->available_codes_count > 0 ? 'Yes (' . $product->available_codes_count . ' available)' : 'No',
                'digital_code' => '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Digital Product Type',
            'Has Code Already',
            'digital_code (fill this)',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:E1')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_BLACK);
        $sheet->getStyle('A1:E1')->getFill()->applyFromArray([
            'fillType' => Fill::FILL_SOLID,
            'color' => ['rgb' => '063C93'],
        ]);
        $sheet->getStyle('E1')->getFont()->setBold(true)->getColor()->setARGB(Color::COLOR_BLACK);

        return [
            'A1:E1' => [
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
                $lastRange = 'A1:E' . $lastRow;

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

                // Highlight the digital_code column (E) with a light-yellow fill to guide the user
                if ($lastRow > 1) {
                    $sheet->getStyle('E2:E' . $lastRow)->getFill()->applyFromArray([
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'FFFBE6'],
                    ]);
                }

                // Lock non-editable columns A–D with a comment hint
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(40);

                $sheet->getRowDimension(1)->setRowHeight(25);
            },
        ];
    }
}
