<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DebateRoom;
use App\Models\Argument;
use App\Models\User;
use App\Jobs\ProcessAiDebate;

class ArenaController extends Controller
{
    // DASHBOARD
    public function dashboard()
    {
        $myRooms = DebateRoom::whereHas('users', function($q) {
            $q->where('user_id', auth()->id())->where('role', 'prompter');
        })->orderBy('created_at', 'desc')->get();

        $otherRooms = DebateRoom::whereDoesntHave('users', function($q) {
            $q->where('user_id', auth()->id())->where('role', 'prompter');
        })->orderBy('created_at', 'desc')->get();
        
        $stats = [
            'total_rooms' => DebateRoom::count(),
            'total_args' => Argument::whereNotNull('participant_id')->orWhereNotNull('stance')->count(),
            'total_users' => User::count(),
        ];
        
        return view('dashboard', compact('myRooms', 'otherRooms', 'stats'));
    }

    // HALAMAN PANDUAN
    public function panduan()
    {
        return view('panduan');
    }

    // 1. Simpan Ruangan
    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'mode' => 'required|in:debate,discussion',
            'max_rounds' => 'required|integer|min:1|max:10' 
        ]);
        
        $room = DebateRoom::create([
            'topic' => $request->topic,
            'mode' => $request->mode,
            'status' => 'live', 
            'max_rounds' => $request->max_rounds
        ]);

        $room->users()->attach(auth()->id(), ['role' => 'prompter']);

        return redirect()->route('arena.show', $room->id)->with('success', 'Ruangan berhasil dibuat!');
    }

    // 2. Masuk Ruangan
    public function show($id)
    {
        $room = DebateRoom::findOrFail($id);
        
        $membership = $room->users()->where('user_id', auth()->id())->first();
        if (!$membership) {
            $room->users()->attach(auth()->id(), ['role' => 'audience']);
            $userRole = 'audience';
        } else {
            $userRole = $membership->pivot->role;
        }

        return view('arena.show', compact('room', 'userRole'));
    }

    // 3. Tarik Argumen (AJAX)
    public function getArguments($id)
    {
        $args = Argument::where('debate_room_id', $id)->orderBy('created_at', 'asc')->get();
        
        $args->transform(function($arg) {
            $arg->likes_count = DB::table('argument_likes')->where('argument_id', $arg->id)->count();
            $arg->is_liked = DB::table('argument_likes')->where('argument_id', $arg->id)->where('user_id', auth()->id())->exists();
            return $arg;
        });

        return $args;
    }

    // 4. Trigger AI
    public function startAi($id)
    {
        $room = DebateRoom::findOrFail($id);
        
        $membership = $room->users()->where('user_id', auth()->id())->first();
        if (!$membership || $membership->pivot->role !== 'prompter') abort(403);

        ProcessAiDebate::dispatch($room->id, 1, 'pro');
        return back();
    }

    // 5. Angkat Moderator
    public function promote($id, $userId)
    {
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        
        if (!$me || $me->pivot->role !== 'prompter') abort(403);

        $room->users()->updateExistingPivot($userId, ['role' => 'moderator']);
        return back();
    }

    // 6. Like Argumen
    public function toggleLike($id, $argId)
    {
        $exists = DB::table('argument_likes')->where('argument_id', $argId)->where('user_id', auth()->id())->first();

        if ($exists) {
            DB::table('argument_likes')->where('id', $exists->id)->delete();
        } else {
            DB::table('argument_likes')->insert([
                'argument_id' => $argId, 'user_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        return response()->json(['success' => true]);
    }

    // 7. Simpan Komentar
    public function storeComment(Request $request, $id)
    {
        $request->validate(['content' => 'required|string|max:500']);
        DB::table('comments')->insert([
            'debate_room_id' => $id, 'user_id' => auth()->id(), 'content' => $request->content, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    }

    // 8. Ambil Komentar
    public function getComments($id)
    {
        return DB::table('comments')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('debate_room_user', function($join) use ($id) {
                $join->on('users.id', '=', 'debate_room_user.user_id')
                     ->where('debate_room_user.debate_room_id', '=', $id);
            })
            ->where('comments.debate_room_id', $id)
            ->orderBy('comments.created_at', 'asc')
            ->select('comments.*', 'users.name as user_name', 'debate_room_user.role as user_role')
            ->get();
    }

    // 9. Hapus Komentar
    public function destroyComment($id, $commentId)
    {
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        if (!$me || !in_array($me->pivot->role, ['prompter', 'moderator'])) abort(403);

        DB::table('comments')->where('id', $commentId)->delete();
        return response()->json(['success' => true]);
    }

    // 10. Hapus Ruangan
    public function destroy($id)
    {
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        if (!$me || $me->pivot->role !== 'prompter') abort(403);

        $room->delete();
        return redirect()->route('dashboard')->with('success', 'Ruangan dihapus.');
    }

    // 11. Follow-Up Question dari Prompter
    public function followUp(Request $request, $id)
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000'
            ]);

            $room = DebateRoom::findOrFail($id);
            $me = $room->users()->where('user_id', auth()->id())->first();
            
            if (!$me || $me->pivot->role !== 'prompter') {
                return response()->json(['error' => 'Akses Ditolak'], 403);
            }

            $lastTurn = Argument::where('debate_room_id', $room->id)->max('turn_order') ?? 0;
            $newTurnOrder = $lastTurn + 1;

            Argument::create([
                'debate_room_id' => $room->id,
                'participant_id' => null, 
                'user_id' => auth()->id(),
                'stance' => 'prompter',
                'turn_order' => $newTurnOrder,
                'content' => $request->content
            ]);

            ProcessAiDebate::dispatch($room->id, $newTurnOrder + 1, 'ai_answer');
            
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'PESAN_ERROR_ASLI' => $e->getMessage(),
                'FILE_YANG_ERROR' => $e->getFile(),
                'BARIS_KE' => $e->getLine()
            ], 500);
        }
    }
}