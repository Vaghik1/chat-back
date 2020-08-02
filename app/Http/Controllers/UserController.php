<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use JWTAuthException;
use Illuminate\Support\Str;
use Validator, DB, Hash, Mail, Cookie, Exception;

class UserController extends Controller
{
    private function getToken($email, $password)
    {
        $token = null;
        try {
            if (!$token = JWTAuth::attempt( ['email'=>$email, 'password'=>$password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid',
                    'token'=>$token
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }
    
    public function login(Request $request)
    {   
        try{
            $user = \App\User::where('email', $request->email)->get()->first();
            if ($user && \Hash::check($request->password, $user->password)) {
                $token = self::getToken($request->email, $request->password);
                $user->auth_token = $token;
                $user->save();
                $response = ['success' => true, 'data' => [
                    'id' => $user->id,
                    'auth_token' => $token,
                    'name' => $user->name, 
                    'email' => $user->email
                ]];      
                Cookie::queue('token', $token);     
                return response()->json($response);
            } else {
                throw new Exception('Wrong email or password');
            }
        } catch(\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function verifyUser(Request $request)
    {
        try {
            $verification_code = $request->verification_code;
            $check = DB::table('user_verifications')->where('token', $verification_code)->first();

            if(!is_null($check)){
                $user = User::find($check->user_id);

                if($user->is_verified == 1){
                    throw new Exception('Account already verified');
                }

                $user->update(['is_verified' => 1]);
                DB::table('user_verifications')->where('token',$verification_code)->delete();

                return response()->json([
                    'success'=> true,
                    'message'=> 'You have successfully verified your email address.'
                ]);
            } else {
                throw new Exception('Wrong token');
            }
        } catch(\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function register(Request $request)
    { 
        try {
            $credentials = $request->only('name', 'email', 'password');
            
            $rules = [
                'name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users'
            ];

            $validator = Validator::make($credentials, $rules);
            if($validator->fails()) {
                return response()->json(['success'=> false, 'error'=> $validator->messages()], 400);
            }

            $name = $request->name;
            $email = $request->email;
            $password = $request->password;

            $payload = [
                'password' => \Hash::make($password),
                'email' => $email,
                'name' => $name,
                'auth_token' => ''
            ];

            $user = new \App\User($payload);
            if ($user->save()) {
                
                $token = self::getToken($email, $password);
                
                if (!is_string($token)) {
                    throw new Exception('Token generation failed');
                }
                
                $user = \App\User::where('email', $email)->get()->first();
                
                $user->auth_token = $token;
                
                $user->save();

                $verification_code = Str::random(30); 
                DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

                $subject = "Please verify your email address.";
                Mail::send('email.verify', [
                    'name' => $name, 
                    'verification_code' => $verification_code,
                    'url' => request()->headers->get('origin')
                ],
                function($mail) use ($email, $name, $subject){
                    $mail->from(env('MAIL_FROM_ADDRESS'), "Chat Company");
                    $mail->to($email, $name);
                    $mail->subject($subject);
                });
                
                $response = ['success' => true, 'data' => [
                    'name' => $name,
                    'id' => $user->id,
                    'email' => $email
                ]];        
            } else {
                throw new Exception('Couldnt register user');
            }
        } catch(\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}