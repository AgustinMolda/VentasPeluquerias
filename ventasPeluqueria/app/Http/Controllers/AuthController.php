<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller
{
    public function register(Request $request){
        

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->passowrd)
        ]);


        try{
            $token = JWTAuth:: fromUser($user);

        }catch(JWTException $e){
            return response()->json(['error' => 'No se pudo crear el token' ],500);
        }

        return response()->json([
            'token' => $token,
            'user' => $user
        ],201);
    }

    public function login(Request $request){

        $credentials =  $request -> only('email','password');

        try{
                if(! $token = JWTAuth:: attempt($credentials)){
                    return response()->json(['error' => 'Invalid credentials']);
                }
        }catch(JWTException $e){
            return response()->json(['error' => 'No se pudo crear el token']);
        }


        return response()->json([
            'token' => $token,
            'expires_in' => JWTAuth::factory()->getTTl() *60,
        ]);
    }


    public function logout(){
        try{
            JWTAuth:: invalidate(JWTAuth::getToken());
        }catch(JWTException $e){
            return response()->json(['error' => 'Error al cerrar sesión, intente de nuevo'],500);
        }

        return response()->json([ 'message' => 'Sesión cerrada correctamente' ]);
    }


    public function getUser(){

        try{
            $user=Auth::user(); 

            if(!$user){
                return response()->json(['error' => 'Usuario no encontrado'],404);
            }
        }catch(JWTException $e){
                return  response ()-> json([ 'error' => 'Error al obtener el perfil del usuario' ], 500 ); 
            }

            
    }

  
    /*public function updateUser(Request $request){
        try {
            $user = Auth::user();
            $user->update($request->only(['name','email']));
            return response()->json($user);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }*/

        

}
