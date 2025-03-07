<?php

namespace App\Filters\V1;

use App\Filters\ApiFilter;
use Illuminate\Http\Request;

class HotelFilter extends ApiFilter
{
    protected $safeParams = [
        'title' => 'eq',
        'checkin' => 'eq',
        'checkout' => 'eq',
        'address' => 'eq',
        'email' => 'eq',
        'phone' => 'eq',
        'rating' => ['eq', 'lt', 'gt', 'lte'],
        'lng' => 'eq',
        'lat' => 'eq',
        'early_in' => 'eq',
        'early_out' => 'eq',
        'type' => 'eq'
    ];

    protected $columnMap = [
        'rating' => 'rating',
    ];

    protected $operatorMap = [
        'eq' => '=',
        'lt' => '<',
        'lte' => '<=',
        'gte' => '>=',
        'in' => 'in',
        'not_in' => 'not in',
        'gt' => '>'
    ];

    public function transform(Request $request)
    {
        $elQuery = [];

        foreach ($this->safeParams as $param => $operators) {
            $query = $request->query($param);
            if (!isset($query)) {
                continue;
            }

            $column = $this->columnMap[$param] ?? $param;

            foreach ($operators as $operator) {
                if (isset($query[$operator])) {
                    $elQuery[] = [$column, $this->operatorMap[$operator], $query[$operator]];
                }
            }
        }
        return $elQuery;
    }

}

