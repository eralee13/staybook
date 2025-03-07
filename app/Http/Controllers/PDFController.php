<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function generatePDF($id)
    {
        $book = Book::where('id', $id)->first();
        $date = date('d/m/Y H:i');
        $data = [
            'title' => 'StayBook',
            'date' => date('d/m/Y H:i'),
            'book' => $book
        ];
        $pdf = PDF::loadView('pdf.book', $data);
        return $pdf->download('book_'. $book->book_id .'_'.$date.'.pdf');
    }
}
