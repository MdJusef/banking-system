<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function create_user(UserRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->account_type = $request->account_type;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json($user,201);
    }

    public function login(LoginRequest $request)
    {
        $credentials = request(['email','password']);

        if(! $token = auth()->attempt($credentials)){
            return response()->json(['error' => 'Unauthorized'],401);
        }
        return $this->responseWithToken($token);
    }

    protected function responseWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 3600
        ]);
    }
}
