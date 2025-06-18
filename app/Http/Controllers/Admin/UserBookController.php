<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookCancelMail;
use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
        $books = Book::where('user_id', $user)->where('sum', '!=', 0)->get();
        return view('auth.userbooks.index', compact('books'));
    }

    public function showBook($id)
    {
        $book = Book::where('id', $id)->firstOrFail();
        return view('auth.userbooks.show', compact('book'));
    }

    public function cancel_calculate(Request $request, Book $book)
    {
        return view('auth.userbooks.cancel-calculate', compact('book', 'request'));
    }

    public function cancel_confirm(Request $request, Book $book)
    {
        $user = Auth::id();
        $books = Book::where('user_id', $user)->where('status', 'Reserved')->get();
        Book::where('id', $book->id)->update(['status' => 'Cancelled']);
        Log::warning('Отмена брони: ' . $book->id);
        //Mail::to('myrzabekova@silkwaytravel.kg')->send(new BookCancelMail($book));
        session()->flash('success', 'Booking ' . $request->title . ' is cancelled');
        return redirect()->route('auth.userbooks.index', compact('books'));
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
                return view('auth.userbooks.cancel-calculate-exely', compact('calc', 'request', 'book'));
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
                $book->update([
                    'status' => "Cancelled"
                ]);
                Log::warning('Отмена брони: ' . $book->id);
                //Mail::to('myrzabekova@silkwaytravel.kg')->send(new BookCancelMail($book));
                $books = Book::all();

                return view('pages.booking.exely.cancel-confirm', compact('cancel'));
            }

        } catch (RequestException $e) {
            Log::error('Ошибка запроса: ' . $e->getMessage());

            return response()->json(['error' => 'Сервис временно недоступен'], 503);
        }
    }

}
