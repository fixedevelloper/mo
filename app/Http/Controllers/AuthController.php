<?php


namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // -----------------------------
    // Login
    // -----------------------------
    public function login(Request $request)
    {

        $request->validate([
            'phone'=>'required|string',
            'password'=>'required|string',
        ]);
        logger($request->all());
        $user = User::where('phone',$request->phone)->first();

        if(!$user || !Hash::check($request->password,$user->password)){
            throw ValidationException::withMessages([
                'phone'=>['Numéro ou mot de passe incorrect.']
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        $user->update(['last_login_at'=>now()]);

        return response()->json([
           'data'=>[
               'user'=>$user,
               'token'=>$token
           ]
        ]);
    }

    // -----------------------------
    // Logout
    // -----------------------------
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=>'Déconnexion réussie']);
    }

    // -----------------------------
    // Infos du profil connecté
    // -----------------------------
    public function me(Request $request)
    {
        $user = $request->user();
        if($user->role === 'client'){
            $user->load('customer.addresses');
        }else{
            $user->load('agent.zone');
        }

        return response()->json($user);
    }
}
