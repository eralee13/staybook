<?php

namespace App\Filters\V1;

use Illuminate\Http\Request;

class HotelFilter
{
    protected $safeParams = [
        'city' => ['eq'],
        'rating' => ['eq', 'gt', 'lt'],
        'type' => ['eq'],
    ];

    protected $columnMap = [
        'city' => 'city',
        'rating' => 'rating',
        'type' => 'type',
    ];

    public function transform(Request $request)
    {
        $query = [];

        // Если обычный JSON-запрос
        foreach ($this->safeParams as $param => $operators) {
            $value = $request->query($param);
            if ($value !== null) {
                $query[] = [$this->columnMap[$param], '=', $value];
            }
        }

        return $query;
    }

    public function fromArray(array $items): array
    {
        $query = [];

        foreach ($items as $item) {
            foreach ($this->safeParams as $param => $operators) {
                if (isset($item[$param])) {
                    $query[] = [$this->columnMap[$param], '=', $item[$param]];
                }
            }
        }

        return $query;
    }
}