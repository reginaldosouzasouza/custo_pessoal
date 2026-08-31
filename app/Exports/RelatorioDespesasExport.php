<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RelatorioDespesasExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
{
    public function __construct(
        private Collection $itens
    ) {
    }

    public function collection()
    {
        return $this->itens;
    }

    public function headings(): array
    {
        return [
            'Descrição',
            'Categoria',
            'Origem',
            'Vencimento',
            'Pagamento',
            'Conta',
            'Situação',
            'Valor',
        ];
    }

    public function map($item): array
    {
        return [
            $item['descricao'] ?? '-',
            $item['categoria'] ?? '-',
            $item['origem'] ?? '-',

            !empty($item['vencimento'])
                ? Carbon::parse($item['vencimento'])->format('d/m/Y')
                : '-',

            !empty($item['pagamento'])
                ? Carbon::parse($item['pagamento'])->format('d/m/Y')
                : '-',

            $item['conta'] ?? '-',

            ucfirst($item['situacao'] ?? '-'),

            (float) ($item['valor'] ?? 0),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],

            'H' => [
                'numberFormat' => [
                    'formatCode' => '#,##0.00',
                ],
            ],
        ];
    }
}
