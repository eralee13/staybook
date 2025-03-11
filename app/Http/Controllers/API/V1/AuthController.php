<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Requests\API\V1\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{
//    public function register(RegisterRequest $request): JsonResponse
//    {
//        $input = $request->all();
//        $input['password'] = bcrypt($input['password']);
//        $user = User::create($input);
//        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
//        $success['name'] =  $user->name;
//
//        return $this->sendResponse($success, 'User register successfully.');
//    }

    /**
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            $success['name'] =  $user->name;

            return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }
}
