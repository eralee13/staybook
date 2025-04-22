<?php

namespace App\Exports;

use App\Models\Book;
// use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class BooksExportExcel implements FromView, WithColumnWidths, WithColumnFormatting
{
    protected $month;

    public function __construct($month = null)
    {
        $this->month = $month;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 18,
            'D' => 10,
            'E' => 10,
            'F' => 18,
            'G' => 18,
            'H' => 10,
            'I' => 10,
            'J' => 15,
            'K' => 25,
            'L' => 25,
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'C' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // Дата создания
            // 'F' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // Дата заезда
            // 'G' => NumberFormat::FORMAT_DATE_YYYYMMDD,     // Дата выезда
            'J' => '@',                                     
            'K' => NumberFormat::FORMAT_NUMBER                   
        ];
    }

    public function view(): View
    {
        $query = Book::whereYear('created_at', now()->year);

        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }

        return view('exports.list', ['ebooks' => $query->get(),]);
    }
    

}
