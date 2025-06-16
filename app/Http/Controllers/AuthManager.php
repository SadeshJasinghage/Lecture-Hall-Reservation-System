<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;


class AuthManager extends Controller
{
    //login part

    function login(){
        if (Auth::check()){
            return redirect(route('Home'));
        }
        return view('login');
    }

    function loginPost(Request $request){
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);
    
        $credentials = $request->only('email', 'password');
        $user = User::where('email', $request->email)->first();
    
        if (!$user) {
            return redirect(route('login'))->with("error", "User not found!");
        }
    
        // Check if the user is approved
        if ($user->status === 'pending') {
            return redirect(route('login'))->with("error", "Your registration request is not approved yet.");
        }
    
        if (Auth::attempt($credentials)) {
            // Redirect based on user role
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')->with("success", "Login successful!");
            } elseif ($user->role === 'user') {
                return redirect()->route('user.dashboard')->with("success", "Login successful! ");
            } elseif ($user->role === 'guest') {
                return redirect()->route('guest.dashboard')->with("success", "Login successful!");
            } else {
                return redirect(route('Home'))->with("success", "Login successful!");
            }
        }
    
        return redirect(route('login'))->with("error", "Username or password incorrect!");
    }

    //Registration part
        //get method

    function register(){
        if (Auth::check()){
            return redirect(route('Home'));
        }
        return view('register');
    }

    
    function guestRegister(){
        if (Auth::check()){
            return redirect(route('Home'));
        }
        return view('regGuest');
    }

    function adminRegister(){
        if (Auth::check()){
            return redirect(route('Home'));
        }
        return view('regAdmin');
    }

    //post method

    // user post method
    function registerPost(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role'     => 'required|in:admin,user,guest' 


        ]);
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password);
        $data['role'] = $request->role;
        $data['status'] = ($request->role === 'admin') ? 'approved' : 'pending';
        $user = User::create($data);

        if(!$user){
            $redirectRoute = ($request->role === 'guest')
                            ? route('guestRegister')
                            : (($request->role === 'admin') 
                            ? route('adminRegister') 
                            : route('userRegister'));
            return redirect($redirectRoute)->with("error", "Registration Failed");
        }
        return redirect(route('login'))->with("success","Registration Form Send to the Admin. Please wait for confermation email..");
    }

    //guest post method

    function guestRegisterPost(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role'     => 'required|in:admin,user,guest' 


        ]);
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password);
        $data['role'] = $request->role;
        $data['status'] = ($request->role === 'admin') ? 'approved' : 'pending';
        $user = User::create($data);

        if(!$user){
            $redirectRoute = ($request->role === 'user')
                            ? route('userRegister')
                            : (($request->role === 'admin') 
                            ? route('adminRegister') 
                            : route('guestRegister'));
            return redirect($redirectRoute)->with("error", "Registration Failed");
        }
        return redirect(route('login'))->with("success","Registration Form Send to the Admin. Please wait for confermation email..");
    }

    //admin post method

    function adminRegisterPost(Request $request){
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'role'     => 'required|in:admin,user,guest' 


        ]);
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = Hash::make($request->password);
        $data['role'] = $request->role;
        $data['status'] = ($request->role === 'admin') ? 'approved' : 'pending';
        $user = User::create($data);

        if(!$user){
            $redirectRoute = ($request->role === 'guest')
                            ? route('guestRegister')
                            : (($request->role === 'user') 
                            ? route('userRegister') 
                            : route('adminRegister'));
            return redirect($redirectRoute)->with("error", "Registration Failed");
        }
        return redirect(route('login'))->with("success","Registration Success full");
    }



    function logout(){
        Session::flush();
        Auth::logout();
        return redirect(route('login'));
    }

}
