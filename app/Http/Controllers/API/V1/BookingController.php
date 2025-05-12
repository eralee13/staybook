<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\CancelRequest;
use App\Http\Requests\API\V1\StoreBookRequest;
use App\Http\Requests\API\V1\UpdateBookRequest;
use App\Http\Resources\V1\BookingResource;
use App\Models\Book;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * @return Collection
     */
    public function index()
    {
        return BookingResource::collection(Book::all());
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $book = Book::findOrFail($id);
            return response()->json($book);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(StoreBookRequest $request)
    {
        try {
            Book::create($request->validated());
            return response()->json('Book created successfully', 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => $e]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function cancel(CancelRequest $request)
    {
        try {
            $book = Book::where('book_token', $request->book_token)->where('status', 'Reserved')->first();
            if(!$book){
                return response()->json('Not found');
            } else {
                $book->update(['status' => 'Cancelled']);
            }
            return response()->json('Book cancelled');
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Book not found'], 404);
        }

    }
}
