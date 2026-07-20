<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\DebateRoom;
use App\Models\Argument;
use App\Models\User;
use App\Jobs\ProcessAiDebate;
use App\Http\Controllers\ProfileController;

// PERBAIKAN: Langsung arahkan ke Dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// DASHBOARD (Menarik Ruangan & Statistik Sistem)
Route::get('/dashboard', function () {
    $myRooms = DebateRoom::whereHas('users', function($q) {
        $q->where('user_id', auth()->id())->where('role', 'prompter');
    })->orderBy('created_at', 'desc')->get();

    $otherRooms = DebateRoom::whereDoesntHave('users', function($q) {
        $q->where('user_id', auth()->id())->where('role', 'prompter');
    })->orderBy('created_at', 'desc')->get();
    
    // Tarik Statistik untuk UAS
    $stats = [
        'total_rooms' => DebateRoom::count(),
        'total_args' => Argument::whereNotNull('participant_id')->orWhereNotNull('stance')->count(),
        'total_users' => User::count(),
    ];
    
    return view('dashboard', compact('myRooms', 'otherRooms', 'stats'));
})->middleware(['auth', 'verified'])->name('dashboard');

// HALAMAN PANDUAN (Baru)
Route::get('/panduan', function () {
    return view('panduan');
})->middleware(['auth', 'verified'])->name('panduan');


Route::middleware('auth')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 1. Simpan Ruangan
    Route::post('/arena/store', function (Request $request) {
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
    })->name('arena.store');

    // 2. Masuk Ruangan
    Route::get('/arena/{id}', function ($id) {
        $room = DebateRoom::findOrFail($id);
        
        $membership = $room->users()->where('user_id', auth()->id())->first();
        if (!$membership) {
            $room->users()->attach(auth()->id(), ['role' => 'audience']);
            $userRole = 'audience';
        } else {
            $userRole = $membership->pivot->role;
        }

        return view('arena.show', compact('room', 'userRole'));
    })->name('arena.show');

    // 3. Tarik Argumen
    Route::get('/arena/{id}/arguments', function ($id) {
        $args = Argument::where('debate_room_id', $id)->orderBy('created_at', 'asc')->get();
        
        $args->transform(function($arg) {
            $arg->likes_count = DB::table('argument_likes')->where('argument_id', $arg->id)->count();
            $arg->is_liked = DB::table('argument_likes')->where('argument_id', $arg->id)->where('user_id', auth()->id())->exists();
            return $arg;
        });

        return $args;
    })->name('arena.arguments');

    // 4. Trigger AI
    Route::post('/arena/{id}/start', function ($id) {
        $room = DebateRoom::findOrFail($id);
        
        $membership = $room->users()->where('user_id', auth()->id())->first();
        if (!$membership || $membership->pivot->role !== 'prompter') abort(403);

        ProcessAiDebate::dispatch($room->id, 1, 'pro');
        return back();
    })->name('arena.start');

    // 5. Angkat Moderator
    Route::post('/arena/{id}/promote/{userId}', function ($id, $userId) {
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        
        if (!$me || $me->pivot->role !== 'prompter') abort(403);

        $room->users()->updateExistingPivot($userId, ['role' => 'moderator']);
        return back();
    })->name('arena.promote');

    // 6. Like
    Route::post('/arena/{id}/argument/{argId}/like', function ($id, $argId) {
        $exists = DB::table('argument_likes')->where('argument_id', $argId)->where('user_id', auth()->id())->first();

        if ($exists) {
            DB::table('argument_likes')->where('id', $exists->id)->delete();
        } else {
            DB::table('argument_likes')->insert([
                'argument_id' => $argId, 'user_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
        return response()->json(['success' => true]);
    });

    // 7. Simpan Komentar
    Route::post('/arena/{id}/comment', function (Request $request, $id) {
        $request->validate(['content' => 'required|string|max:500']);
        DB::table('comments')->insert([
            'debate_room_id' => $id, 'user_id' => auth()->id(), 'content' => $request->content, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['success' => true]);
    });

    // 8. Ambil Komentar
    Route::get('/arena/{id}/comments', function ($id) {
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
    });

    // 9. Hapus Komentar
    Route::delete('/arena/{id}/comment/{commentId}', function ($id, $commentId) {
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        if (!$me || !in_array($me->pivot->role, ['prompter', 'moderator'])) abort(403);

        DB::table('comments')->where('id', $commentId)->delete();
        return response()->json(['success' => true]);
    });

    // 10. Hapus Ruangan
    Route::delete('/arena/{id}', function ($id) {
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        if (!$me || $me->pivot->role !== 'prompter') abort(403);

        $room->delete();
        return redirect()->route('dashboard')->with('success', 'Ruangan dihapus.');
    })->name('arena.destroy');

    // 11. FITUR BARU: FOLLOW-UP QUESTION DARI PROMPTER
    Route::post('/arena/{id}/follow-up', function (Request $request, $id) {
        $request->validate(['content' => 'required|string|max:1000']);
        $room = DebateRoom::findOrFail($id);
        $me = $room->users()->where('user_id', auth()->id())->first();
        
        if (!$me || $me->pivot->role !== 'prompter') abort(403);

        // Simpan pertanyaan Prompter ke DB
        Argument::create([
            'debate_room_id' => $room->id,
            'participant_id' => auth()->id(),
            'stance' => 'prompter',
            'turn_order' => 999, // Penanda order spesial
            'content' => $request->content
        ]);

        // Panggil AI khusus untuk menjawab Follow Up
        ProcessAiDebate::dispatch($room->id, 999, 'ai_answer');
        
        return response()->json(['success' => true]);
    });

});

require __DIR__.'/auth.php';