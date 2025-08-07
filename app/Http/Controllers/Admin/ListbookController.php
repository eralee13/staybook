<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookCancelMail;
use App\Models\Book;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        $books = Book::where('hotel_id', $hotel)->where('api_type', '!=', 'calendar')->latest()->paginate(40);

        return view('auth.listbooks.index', compact('books'));
    }

    public function show($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        $startDate = Carbon::parse($book->arrivalDate);
        $endDate = Carbon::parse($book->departureDate);
        $numberOfDays = $startDate->diffInDays($endDate) + 1;
        return view('auth.listbooks.show', compact('book', 'numberOfDays'));
    }

    public function destroy($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        $book->delete();
        $email = Contact::first()->email;
        Mail::to($email)->send(new BookingDeleteMail($book));
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

    public function cancel_calculate(Request $request, Book $book)
    {
        return view('auth.listbooks.cancel-calculate', compact('book', 'request'));
    }

    public function cancel_confirm(Request $request, Book $book)
    {
        $user = Auth::id();
        $books = Book::where('user_id', $user)->where('status', 'Reserved')->get();
        Book::where('id', $book->id)->update(['status' => 'Cancelled']);
        Log::warning('Отмена брони: ' . $book->id);
        $email = Contact::first()->email;
        Mail::to($email)->send(new BookCancelMail($book));
        session()->flash('success', 'Booking ' . $request->title . ' is cancelled');
        return redirect()->route('auth.listbooks.index', compact('books'));
    }

    //exely
    public function cancel_calculate_exely(Request $request, Book $book)
    {
        try {
            $cancel = Carbon::createFromDate(now())->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z');

            $response = Http::timeout(30)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->get(config('services.exely.base_url') . 'reservation/v1/bookings/' . $book->book_token . '/calculate-cancellation-penalty?cancellationDateTimeUtc=' . $cancel);
            if ($response->successful()) {
                $calc = $response->object();
                return view('auth.listbooks.cancel-calculate-exely', compact('calc', 'request', 'book'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('errors.400', compact('response'));
            }
        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());
            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

    public function cancel_confirm_exely(Request $request, Book $book)
    {
        try {
            $response = Http::timeout(60)
                ->withHeaders(['x-api-key' => config('services.exely.key'), 'accept' => 'application/json'])
                ->post(config('services.exely.base_url') . 'reservation/v1/bookings/' . $request->number . '/cancel', [
                    "reason" => "Booking cancellation",
                    "expectedPenaltyAmount" => $request->amount
                ]);

            if ($response->successful()) {
                $cancel = $response->object();
                Book::where('book_token', $cancel->booking->number)->update(['status' => 'Cancelled']);
                Log::warning('Отмена брони: ' . $book->id);
                $email = Contact::first()->email;
                Mail::to($email)->send(new BookCancelMail($book));
                return view('auth.listbooks.cancel-confirm-exely', compact('cancel'));
            } else {
                Log::warning('Запрос завершился ошибкой: ' . $response->status());
                return view('errors.400', compact('response'));
            }

        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());

            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }
}
