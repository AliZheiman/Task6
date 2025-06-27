<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\AuthApiTrait;
use Tymon\JWTAuth\Facades\JWTAuth;
class AuthController extends Controller
{
    use AuthApiTrait;

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|min:3|max:20',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6',
            'role'                  => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => $data['role'],
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->successResponse([
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token
        ], 'Registered Successfully', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->successResponse(null, 'Invalid credentials', 401);
        }

        $user = JWTAuth::user();

        return $this->successResponse([
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'token' => $token
        ], 'Logged in Successfully');
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse(null, 'Logged out Successfully');
    }

    public function getProfile()
    {
        return $this->successResponse(JWTAuth::user());
    }
}
