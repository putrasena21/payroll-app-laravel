<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\UserHrd;
use App\Models\VerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if($validated->fails()){
            return ResponseFormatter::createResponse(400,'Bad Request', ['errors'=>$validated->errors()->all()]);
        };

        $hrd = UserHrd::where('email', $request->email)->first();
        if (! $hrd) {
            return ResponseFormatter::createResponse(401, 'Unauthorized');
        }

        if(! Hash::check($request->password, $hrd->password)){
            $hrd->failed_login_attempt += 1;
            $hrd->save();

            return ResponseFormatter::createResponse(401, 'Your email or password is wrong');
        }

        if($hrd->status == 'DELETED'){
            return ResponseFormatter::createResponse(401, 'Unauthorized');
        }

        // updating login
        if($hrd->is_login){
            VerificationToken::where('user_id', $hrd->id)->delete();
        }

        $token = $hrd->createToken('token')->plainTextToken;
        VerificationToken::create([
            'user_id' => $hrd->id,
            'token' => $token,
        ]);

        $hrd->is_login = 1;
        $hrd->failed_login_attempt = 0;
        $hrd->save();

        $response = [
            'id' => $hrd->id,
            'username' => $hrd->username,
            'full_name' => $hrd->full_name,
            'email' => $hrd->email,
            'token' => $token,
        ];

        return ResponseFormatter::createResponse(200, 'Success login hrd', $response);
    }

    public function register(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'username' => 'required|string|min:4|unique:user_hrds,username',
            'full_name' => 'required|string|min:3',
            'email' => 'required|email|unique:user_hrds,email',
            'password' => ['required','string', Password::min(8)->mixedCase()->numbers()],
        ]);

        if($validated->fails()) {
            return ResponseFormatter::createResponse(400, 'Bad Request', ['errors'=>$validated->errors()->all()]);
        }

        $newHrd = UserHrd::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return ResponseFormatter::createResponse(201, 'success', $newHrd);
    }
}