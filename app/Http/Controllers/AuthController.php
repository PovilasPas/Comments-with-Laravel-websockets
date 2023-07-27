<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\View;

class AuthController extends Controller
{
    public function ShowAuthForm()
    {
        return view('authenticate');
    }

    public function Authenticate(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|min:10'
        ], [
            'email.required' => 'Email is required',
            'email.email' => 'Not a valid email format',
            'password.required' => 'Password is required',
            'password.min' => 'Pasword has to be at least 10 characters long'
        ]);
        $user = User::where('email', '=', $fields['email'])->first();
        if($user === null) 
        {
            $temp = $fields['password'];
            $fields['password'] = Hash::make($fields['password']);
            User::create($fields);
            $fields['password'] = $temp;
        }
        if(auth()->attempt($fields))
        {
            $request->session()->regenerate();
            return redirect('/comments');
        }
        else return back()->withInput($fields)->withErrors(['authentication' => 'Invalid authentication data']);
    }

    public function Logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
