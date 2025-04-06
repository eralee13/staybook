<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
        $books = Book::where('user_id', $user)->orderBy('id', 'desc')->get();
        // $books = Book::where('user_id', $user)->where('status', 'Reserved')->get();
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
        $message;

        if ( $request->api_type == 'tourmind' ){
            $res = $this->cancelOrderTm($request, $book);
            
            if ( isset($res['Error']['ErrorMessage']) ){

                $message = $res['Error']['ErrorMessage'];
        
             }
             elseif( isset($res['CancelResult']['OrderStatus']) && $res['CancelResult']['OrderStatus'] == 'CANCELLED'){

                $cancelFee = $res['CancelResult']['CancelFee'];
                $curr = $res['CancelResult']['CurrencyCode'];

                Book::where('id', $book->id)->update(['status' => 'Cancelled', 'cancelFee' => $cancelFee]);
                $message = "is {$res['CancelResult']['OrderStatus']} CancelFee {$cancelFee} {$curr}";
             }

        }else{

            // $books = Book::where('user_id', $user)->where('status', 'Reserved')->get();
            // Book::where('id', $book->id)->update(['status' => 'Cancelled']);
            // session()->flash('success', 'Booking ' . $request->title . ' is cancelled');

        }
        session()->flash('success', $message);
        $books = Book::where('user_id', $user)->orderBy('id', 'desc')->get();
        return redirect()->route('userbooks.index', compact('books'));
    }

    public function cancelOrderTm($request, $book){

        // cancel order from tourmind
        $this->baseUrl = config('app.tm_base_url');
        $userId = Auth::id();
        

       try {
        
        $agent = "swt-".$userId;
        $reservId = $book->revervation_id;
        $token = $book->book_token;

            $payload = [
                "AgentRefID" => $agent,
                "RequestHeader" => [
                    "AgentCode" => "tms_test",
                    "Password" => "tms_test",
                    "UserName" => "tms_test",
                    "TransactionID" => $token,
                    "RequestTime" => now()->format('Y-m-d H:i:s')
                ]
            ];
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post("{$this->baseUrl}/CancelOrder", $payload);
    
            // if ( $response->failed() ) {
            //     return ['error' => 'CancelOrder Ошибка при запросе к API', 'status' => $response->status()];
            // }

            $data = $response->json();

            return $data;
            

        } catch (\Throwable $th) {
            session->flash('error', "TM CancelOrder Ошибка при запросе к API: " . $th->getMessage());
            // throw new \Exception("TM CancelOrder Ошибка при запросе к API: " . $th->getMessage(), 0, $th);
           }
        
    }

}
