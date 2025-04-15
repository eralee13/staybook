<?php

namespace App\Exports;

use App\Models\Book;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BooksExportExcel implements FromView
{
    protected $month;

    public function __construct($month = null)
    {
        $this->month = $month;
    }

    public function view(): View
    {
        $query = Book::whereYear('created_at', now()->year);

        if ($this->month) {
            $query->whereMonth('created_at', $this->month);
        }
        
        $books = $query->get();
        return view('exports.list', compact('books'));
    }

}
