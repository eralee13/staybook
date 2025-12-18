<?php

namespace App\ViewComposers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HotelsComposer
{
    public function __construct(private Request $request) {}

    public function compose(View $view): void
    {
        $hotel_id = $this->request->session()->get('hotel_id');
        $user = Auth::user();

        // какие поля реально нужны в списках — оставь минимум
        $baseSelect = ['id', 'title', 'title_en', 'city', 'rating', 'image', 'user_id', 'status'];

        // Гость: НЕ тянем все отели. Обычно достаточно “витрины”/топа
        if (!$user) {
            $hotels = cache()->remember('showcase_hotels_v1', 300, function () use ($baseSelect) {
                return Hotel::query()
                    ->select($baseSelect)
                    ->where('status', 1)
                    ->whereNull('tourmind_id')      // если нужно исключать
                    ->orderByDesc('rating')
                    ->limit(20)
                    ->get()
                    ->map(fn($h) => $h->toArray())  // ✅ в кеш кладём массив, не Eloquent
                    ->all();
            });

            $view->with('hotels', $hotels)->with('hotel_id', $hotel_id);
            return;
        }

        // Админы (id 1 и 3): НЕ тянем all(), делаем пагинацию
        if (in_array($user->id, [1, 3], true)) {
            $hotels = Hotel::query()
                ->select($baseSelect)
                ->orderByDesc('id')
                ->paginate(10);

            $view->with('hotels', $hotels)->with('hotel_id', $hotel_id);
            return;
        }

        // Обычный пользователь: только свои отели
        $hotels = Hotel::query()
            ->select($baseSelect)
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate(10);

        $view->with('hotels', $hotels)->with('hotel_id', $hotel_id);
    }
}