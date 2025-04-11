<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserBookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:show-userbook|cancel-userbook', ['only' => ['show','cancel']]);
        $this->middleware('permission:show-userbook', ['only' => ['show']]);
        $this->middleware('permission:cancel-book', ['only' => ['cancel']]);
    }
    public function index(Request $request)
    {
        $user = Auth::id();
        $books = Book::where('user_id', $user)->get();
        return view('auth.userbooks.index', compact('books'));
    }

    public function showBook($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        return view('auth.userbooks.show', compact('book'));
    }

    public function cancelBook(Request $request, Book $book)
    {
        $user = Auth::id();
        $books = Book::where('user_id', $user)->where('status', 'Reserved')->get();
        Book::where('id', $book->id)->update(['status' => 'Cancelled']);
        session()->flash('success', 'Booking ' . $request->title . ' is cancelled');
        return redirect()->route('userbooks.index', compact('books'));
    }

}
