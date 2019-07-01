<?php

namespace App\Http\Controllers;
use \Illuminate\Http\Request;
use \Illuminate\Support\Facades\Hash;
use App\Transformers\Json;

use App\User;

use Laravel\Lumen\Routing\Controller as BaseController;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
    	$user = new User;
    	$user->name = $request->name;
    	$user->email = $request->email;
    	$user->password = Hash::make($request->password);
    	$user->api_token = 0;
    	$user->save();

    	return json::response($user);
    }

    public function login(Request $request)
    {
    	$user = User::where('email', $request->email)->first();

    	if($user) {
    		if(Hash::check($request->password, $user->password)) {
    			$token = base64_encode(str_random(40));

    			$user->api_token = $token;
    			$user->save();

    			//without Bearer
    			return json::response($user);
    		}
    	} else {
    		return json::exception('Email tidak tersedia');
    	}
    	return json::response($user);
    }
}
