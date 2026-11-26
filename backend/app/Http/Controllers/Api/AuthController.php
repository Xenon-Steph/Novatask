<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
class AuthController extends Controller {
    public function register(Request $request) {
        $data=$request->validate(['name'=>'required|string|max:100', 'email'=>'required|email|max:255|unique:users,email', 'password'=>['required','confirmed',Password::min(10)->mixedCase()->numbers()]]);
        $user=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password'])]);
        Auth::login($user); $request->session()->regenerate();
        return response()->json(['user'=>$user->only('id','name','email')],201);
    }
    public function login(Request $request) {
        $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt($data)) return response()->json(['message'=>'Identifiants invalides.'],422);
        $request->session()->regenerate();
        return response()->json(['user'=>$request->user()->only('id','name','email')]);
    }
    public function me(Request $request) {return response()->json(['user'=>$request->user()->only('id','name','email')]);}
    public function logout(Request $request) {
        Auth::logout(); $request->session()->invalidate(); $request->session()->regenerateToken();
        return response()->json(['message'=>'Déconnecté']);
    }
}
