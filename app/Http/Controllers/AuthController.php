<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view("auth.login");
    }

    public function showRegisterForm()
    {
        return view('auth.register');
        
    }

    public function showForgotForm()
    {
        return view('auth.forgot');
        
    }

    public function register(Request $request) //register
    {
        $data = $request->validate([
            "name" => ["required","string"],
            "email" => ["required","email","string","unique:users,email"],
            "password" => ["required","confirmed"],
        ]);

        $user = User::create([
            "name" => $data["name"],
            "email" => $data["email"],
            "password" => bcrypt($data["password"]),
        ]);

        if($user)
        {
            auth("web")->login($user);
        }

        return redirect(route("home"));
    }

    public function login(Request $request) //login
    {
        $data = $request->validate([
            "email" => ["required","email","string"],
            "password" => ["required"],
        ]);

        if(auth("web")->attempt($data))
        {
            return redirect(route("home"));
        }

        return redirect(route("login"))->withErrors([
            "email" => "Пользователь не найден, либо данные введены не правильно",
        ]);
    }

    public function logout() //logout
    {
        auth("web")->logout();
        return redirect(route("home"));
    }

    public function forgot(Request $request) //forgot password
    {
        $data = $request->validate([
            "email" => ["required","email","string","exists:users"],
        ]);

        $user = User::where(["email" => $data["email"]])->first();
        $password=uniqid();
        $user->password=bcrypt($password);
        $user->save();
        Mail::to($user)->send(new ForgotPassword($password));
        return redirect(route("home"));
    }
    
}
