<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    public function index()
    {
        return Rule::all();
    }

    public function show(Rule $rule){
        if($rule == null){
            abort(404);
        }
        return $rule;
    }

    public function store(Request $request)
    {
        Rule::create($request->all());
        return response('Rule created successfully.', 201);
    }

    public function update(Request $request, Rule $rule)
    {
        $rule->update($request->all());
        return $rule;
    }

    public function destroy(Rule $rule)
    {
        $rule->delete();
        return response(null, 204);
    }
}
