<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ExportBookExcel;
use App\Http\Controllers\Controller;
use App\Models\Book;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Exception;

class AllBookingController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:finance-book', ['only' => ['index']]);
    }


    public function index()
    {
        $books = Book::latest()->paginate(30);
        return view('auth.books.finance.index', compact('books'));
    }

//    public function show($id)
//    {
//        $book = Book::where('id', $id)->firstOrFail();
//        $startDate = Carbon::parse($book->arrivalDate);
//        $endDate = Carbon::parse($book->departureDate);
//        $numberOfDays = $startDate->diffInDays($endDate) + 1;
//        return view('auth.books.finance.show', compact('book', 'startDate', 'endDate', 'numberOfDays'));
//    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function exportExcel()
    {

        // Формируем имя файла
        $fileName = 'books_' . now()->format('d_m_y') . '.xlsx';

        return Excel::download(new ExportBookExcel, $fileName);
    }

}
