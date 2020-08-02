<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use JWTAuthException;
use Illuminate\Support\Str;
use Validator, DB, Hash, Exception;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            $user->update($request->all());
            if($user) {
                return response()->json(['success' => true, 'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'phone' => $user->phone,
                ]]);
            } else {
                throw new Exception('Not authenticated');
            }
        } catch(\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}