<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use JWTAuthException;
use Illuminate\Support\Str;
use Validator, DB, Hash, Exception;
use App\Chat;
use App\Events\MessageSent;

class ChatController extends Controller
{
    public function store(Request $request, User $user)
    {
        try {
            if(!$user) return $this->respondUnprocessable();

            $request->validate([
                'message' => ['required', 'max:180']
            ]);

            $message = [
                'sender_id' => auth()->id(),
                'recipient_id' => $user->id,
                'message' => $request->message
            ];

            $message = Chat::create($message);

            event(new MessageSent($message));

            return response()->json(['success' => true, 'data' => 'message sent successfully']);
        } catch(\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function getMessages(Request $request, User $user)
    {
        try {
            $currUser = auth()->user();

            $messages = DB::table('chats')->where([
                ['sender_id', $currUser->id],
                ['recipient_id', $user->id]
            ])->orWhere([
                ['sender_id', $user->id],
                ['recipient_id', $currUser->id]
            ])->orderBy('created_at', 'desc')->get();
            
            return response()->json(['success' => true, 'data' => [
                'messages' => $messages,
            ]]);
        } catch(\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}