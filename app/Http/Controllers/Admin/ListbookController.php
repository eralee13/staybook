<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingDeleteMail;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class ListbookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:create-book|edit-book|delete-book', ['only' => ['index','show']]);
        $this->middleware('permission:create-book', ['only' => ['create','store']]);
        $this->middleware('permission:edit-book', ['only' => ['edit','update']]);
        $this->middleware('permission:delete-book', ['only' => ['destroy']]);
    }
    public function index(Request $request)
    {
        $hotel = $request->session()->get('hotel_id');
        $books = Book::where('hotel_id', $hotel)->paginate(40);
        $listbooks = Book::where('tag', 'exely')->get();

        return view('auth.listbooks.index', compact('books', 'listbooks'));
    }

    public function show($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        return view('auth.listbooks.show', compact('book'));
    }

    public function exelyshow($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        $response = Http::withHeaders(['x-api-key' => 'fd54fc5c-2927-4998-8132-fb1107fc81c4', 'accept' => 'application/json'])->get('https://connect.test.hopenapi.com/api/content/v1/properties/' . $book->hotel_id);
        $property = $response->object();
        //dd($property);
        return view('auth.listbooks.exelyshow', compact('book', 'property'));
    }

    public function destroy($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        $book->delete();
        //Mail::to('info@timmedia.store')->send(new BookingDeleteMail($book));

        session()->flash('success', 'Booking ' . $book->title . ' deleted');
        return redirect()->route('listbooks.index');
    }

    public function searchbook(Request $request)
    {

        if ($request->ajax()) {
            $data = Book::where('book_id', 'like', '%' . $request->search . '%')
                ->orwhere('title', 'like', '%' . $request->search . '%')
                ->orwhere('id', 'like', '%' . $request->search . '%')
                ->orwhere('start_d', 'like', '%' . $request->search . '%')
                ->orwhere('end_d', 'like', '%' . $request->search . '%')->get();
            if (count($data) > 0) { ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Booking</th>
                        <th>Guests</th>
                        <th>Dates of stay</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?php echo $row->id ?></td>
                            <td># <?php echo $row->book_id ?></td>
                            <td><?php echo $row->title ?></td>
                            <td><?php echo $row->start_d ?> - <?php echo $row->end_d ?></td>
                            <td><?php echo $row->sum ?></td>
                            <td>
                                <ul>
                                    <a href="<?php echo route('listbooks.show', $row->id) ?>" class="more"><i
                                                class="fa-regular
                                fa-pen-to-square"></i> Choose</a>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <h2>No results</h2>
                <?php
            }
        }
    }
}
