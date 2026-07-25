<x-app-layout>
    <div class="bg-gradient-to-r from-gray-900 to-gray-800 border-b border-gray-700 shadow-xl py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <span class="badge badge-primary mb-2 font-bold shadow-lg">🏛️ FORUM ARENA</span>
                <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-purple-400 drop-shadow-md">
                    {{ $room->topic }}
                </h1>
                @if($room->mode === 'discussion')
                    <span class="text-sm text-emerald-400 font-bold tracking-widest uppercase mt-1 block">🤝 Mode Diskusi (Kolaborasi Pakar)</span>
                @else
                    <span class="text-sm text-rose-400 font-bold tracking-widest uppercase mt-1 block">⚔️ Mode Debat (Adu Argumen)</span>
                @endif
            </div>
            
            <div class="flex items-center gap-4">
                @php
                    $roleStyles = [
                        'prompter' => 'border-warning text-warning bg-warning/10',
                        'moderator' => 'border-secondary text-secondary bg-secondary/10',
                        'audience' => 'border-gray-500 text-gray-300 bg-gray-700/30'
                    ];
                    $myRoleStyle = $roleStyles[$userRole] ?? $roleStyles['audience'];
                @endphp
                <div class="border-2 px-4 py-2 rounded-lg font-black uppercase tracking-widest shadow-lg {{ $myRoleStyle }}">
                    🎭 ROLE: {{ $userRole }}
                </div>

                @if($userRole === 'prompter')
                    <form action="{{ route('arena.start', $room->id) }}" method="POST" class="inline" id="btn-pemicu">
                        @csrf
                        <button type="submit" class="btn btn-sm bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-extrabold border-none hover:scale-105 transition-transform shadow-[0_0_15px_rgba(16,185,129,0.6)]">
                            🚀 Mulai Pemicu AI
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="py-6 bg-[#1a1e23] min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
                
                <div class="xl:col-span-3">
                    <div class="card bg-[#21262d] shadow-2xl h-[780px] border border-gray-700 flex flex-col relative overflow-hidden">
                        
                        <!-- Box Debat -->
                        <div class="card-body overflow-y-auto z-10 px-4 md:px-8 space-y-6 flex-1" id="debate-box">
                            <div class="text-center mt-32" id="empty-state">
                                <div class="text-7xl mb-4 animate-bounce">🤖</div>
                                <h3 class="text-3xl font-extrabold text-gray-200">Forum Siap!</h3>
                                <p class="text-gray-500 mt-2 text-lg">Menunggu Prompter memberikan sinyal pemicu kepada mesin AI...</p>
                            </div>
                        </div>

                        <!-- Fitur Tanya Lanjutan Khusus Prompter -->
                        <div id="follow-up-container" class="hidden bg-gray-900 border-t-2 border-indigo-500 p-4 shrink-0 z-20 shadow-[0_-10px_20px_rgba(0,0,0,0.5)]">
                            <h4 class="font-bold text-indigo-400 mb-2 text-sm uppercase tracking-wider flex items-center gap-2">
                                🎙️ Tanya / Tanggapan Lanjutan (Khusus Prompter)
                            </h4>
                            <form id="follow-up-form" class="flex gap-2">
                                <input type="text" id="follow-up-input" class="input input-bordered w-full bg-gray-800 text-white border-gray-600 focus:border-indigo-500" placeholder="Tanyakan hal spesifik ke AI terkait hasil diskusi tadi..." required autocomplete="off">
                                <button type="submit" class="btn bg-indigo-600 hover:bg-indigo-700 text-white border-none shadow-lg">Kirim ke AI</button>
                            </form>
                        </div>

                        <div id="loading-indicator" class="hidden absolute bottom-[5.5rem] left-1/2 transform -translate-x-1/2 bg-gray-800 px-6 py-3 rounded-full border border-gray-600 shadow-[0_0_20px_rgba(0,0,0,0.8)] z-30 flex items-center gap-3">
                            <span class="loading loading-bars loading-sm text-accent"></span>
                            <span class="text-sm font-bold text-gray-300 uppercase tracking-wider">AI sedang merakit tanggapan...</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 flex flex-col h-[780px]">
                    <div class="card bg-gradient-to-br from-gray-800 to-gray-900 shadow-2xl border border-gray-700 shrink-0">
                        <div class="card-body p-4 items-center text-center">
                            <h2 class="card-title text-gray-400 text-xs tracking-widest font-bold">GILIRAN AI</h2>
                            <div class="text-5xl font-black text-transparent bg-clip-text bg-gradient-to-br from-white to-gray-500 my-1" id="turn-counter">0</div>
                        </div>
                    </div>

                    @if($userRole === 'prompter')
                    <div class="card bg-[#21262d] shadow-xl border border-gray-700 shrink-0">
                        <div class="card-body p-4">
                            <h3 class="font-bold text-gray-400 text-xs mb-2 uppercase tracking-wider">👥 Manajemen Pengunjung</h3>
                            <div class="overflow-y-auto max-h-32">
                                <table class="table table-xs w-full">
                                    <tbody id="visitor-list">
                                        @foreach($room->users as $participant)
                                            @if($participant->pivot->role !== 'prompter')
                                            <tr class="border-b border-gray-700">
                                                <td class="text-gray-300 font-semibold px-0 flex items-center gap-1">
                                                    {{ $participant->name }}
                                                    @if($participant->pivot->role === 'moderator')
                                                        <span class="badge badge-secondary badge-xs font-black ml-1">MOD</span>
                                                    @endif
                                                </td>
                                                <td class="text-right px-0">
                                                    @if($participant->pivot->role === 'audience')
                                                    <form action="{{ route('arena.promote', ['id' => $room->id, 'userId' => $participant->id]) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-xs btn-outline btn-secondary font-bold">Angkat</button>
                                                    </form>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($room->users->where('pivot.role', '!=', 'prompter')->count() === 0)
                                    <p class="text-xs text-gray-600 text-center italic py-2">Belum ada pengunjung.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="card bg-[#21262d] shadow-xl border border-gray-600 flex-1 flex flex-col overflow-hidden">
                        <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 font-bold text-sm tracking-wider text-gray-300">
                            💬 FORUM CHAT
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-3 space-y-3" id="live-chat-box"></div>

                        <div class="bg-gray-900 p-3 border-t border-gray-700">
                            <form id="chat-form" class="flex gap-2">
                                <input type="text" id="chat-input" class="input input-sm input-bordered w-full bg-gray-800 text-gray-200" placeholder="Ketik pesan..." required autocomplete="off">
                                <button type="submit" class="btn btn-sm btn-primary">Kirim</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomId = {{ $room->id }};
            const myRole = '{{ $userRole }}';
            
            const debateBox = document.getElementById('debate-box');
            const emptyState = document.getElementById('empty-state');
            const loadingIndicator = document.getElementById('loading-indicator');
            const turnCounter = document.getElementById('turn-counter');
            const chatBox = document.getElementById('live-chat-box');
            const followUpContainer = document.getElementById('follow-up-container');
            const btnPemicu = document.getElementById('btn-pemicu');
            
            let lastArgumentCount = 0;
            const maxTurns = {{ $room->max_rounds * 2 }};

            const followUpForm = document.getElementById('follow-up-form');
            if (followUpForm) {
                followUpForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const input = document.getElementById('follow-up-input');
                    const text = input.value.trim();
                    if(!text) return;

                    fetch(`/arena/${roomId}/follow-up`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ content: text })
                    }).then(() => {
                        input.value = '';
                        fetchArguments();
                    });
                });
            }

            window.toggleLike = function(argId) {
                fetch(`/arena/${roomId}/argument/${argId}/like`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                }).then(() => fetchArguments(true)); 
            };

            function fetchArguments(forceUpdateLikes = false) {
                fetch(`/arena/${roomId}/arguments`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(data => {
                    const actualTurns = data.filter(d => !['kesimpulan', 'prompter', 'ai_answer'].includes(d.stance)).length;
                    turnCounter.innerText = actualTurns;

                    const hasConclusion = data.some(d => d.stance === 'kesimpulan');
                    
                    if (hasConclusion && myRole === 'prompter') {
                        if (btnPemicu) btnPemicu.classList.add('hidden');
                        if (followUpContainer) followUpContainer.classList.remove('hidden');
                    }

                    const lastStance = data.length > 0 ? data[data.length - 1].stance : null;
                    if ((actualTurns > 0 && actualTurns < maxTurns && !hasConclusion) || lastStance === 'prompter') {
                        loadingIndicator.classList.remove('hidden');
                    } else {
                        loadingIndicator.classList.add('hidden');
                    }

                    data.forEach(arg => {
                        const likeBtn = document.getElementById(`like-btn-${arg.id}`);
                        if (likeBtn) {
                            likeBtn.className = `btn btn-xs mt-2 ${arg.is_liked ? 'btn-error shadow-lg' : 'btn-outline border-gray-600 text-gray-400'}`;
                            likeBtn.innerHTML = `❤️ ${arg.likes_count}`;
                        }
                    });

                    if (data.length > lastArgumentCount) {
                        if (emptyState) emptyState.remove();
                        const newArgs = data.slice(lastArgumentCount);
                        
                        newArgs.forEach(arg => {
                            if (arg.stance === 'kesimpulan') {
                                debateBox.innerHTML += `
                                    <div class="mt-8 mb-4 w-full">
                                        <div class="bg-gradient-to-r from-yellow-600 to-orange-500 rounded-2xl shadow-[0_0_30px_rgba(234,179,8,0.3)] p-8 text-center border-2 border-yellow-400">
                                            <h3 class="text-yellow-100 font-extrabold tracking-widest text-sm mb-4 uppercase">🏆 Kesimpulan Akhir (Moderator)</h3>
                                            <p class="text-white text-xl md:text-2xl font-black leading-relaxed">"${arg.content}"</p>
                                        </div>
                                    </div>
                                `;
                                return; 
                            }

                            if (arg.stance === 'prompter') {
                                debateBox.innerHTML += `
                                    <div class="mt-6 mb-2 w-full flex justify-center">
                                        <div class="bg-indigo-900/60 border-2 border-indigo-500 rounded-2xl p-5 text-center max-w-[80%] shadow-lg">
                                            <span class="text-xs text-indigo-300 font-bold uppercase tracking-widest block mb-2">🎙️ Pertanyaan Sutradara / Prompter</span>
                                            <p class="text-white text-lg font-medium">${arg.content.replace(/\n/g, '<br>')}</p>
                                        </div>
                                    </div>
                                `;
                                return;
                            }

                            if (arg.stance === 'ai_answer') {
                                let formatted = arg.content.replace('🤖 [Jawaban Panelis AI]\n\n', '').replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<span class="text-warning font-black bg-warning/10 px-1 rounded">$1</span>');
                                debateBox.innerHTML += `
                                    <div class="chat chat-start drop-shadow-xl w-full mb-6">
                                        <div class="chat-header mb-2 flex items-center gap-2">
                                            <span class="badge badge-accent badge-sm font-black text-gray-900 shadow-lg px-3 py-3">JAWABAN AI</span>
                                            <span class="text-xs md:text-sm text-accent font-bold uppercase tracking-wider">Panelis Gabungan</span>
                                        </div>
                                        <div class="chat-bubble bg-teal-900/40 text-gray-100 p-5 md:p-6 text-base md:text-lg border-l-4 border-r-4 border-teal-500 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.3)] max-w-[95%] md:max-w-[85%] leading-relaxed backdrop-blur-sm">
                                            ${formatted}
                                        </div>
                                    </div>
                                `;
                                return;
                            }

                            const isPro = arg.stance === 'pro'; 
                            const isDiscussionMode = '{{ $room->mode }}' === 'discussion';
                            
                            const bubbleLayout = isPro ? 'chat-start' : 'chat-end';
                            const bubbleColor = isDiscussionMode ? (isPro ? 'bg-emerald-900/40' : 'bg-teal-900/40') : (isPro ? 'bg-blue-900/40' : 'bg-rose-900/40');
                            const borderColor = isDiscussionMode ? (isPro ? 'border-emerald-500' : 'border-teal-500') : (isPro ? 'border-blue-500' : 'border-rose-500');
                            const badgeColor = isDiscussionMode ? 'badge-accent' : (isPro ? 'badge-info' : 'badge-error');
                            const stanceLabel = isDiscussionMode ? '💡 GAGASAN' : (isPro ? 'TIM PRO' : 'TIM KONTRA');
                            
                            const contentParts = arg.content.split('\n\n');
                            const aiNameRaw = contentParts.length > 1 ? contentParts[0].replace('🤖 [Ditenagai oleh ', '').replace(']', '') : 'Unknown AI';
                            const mainContent = contentParts.length > 1 ? contentParts.slice(1).join('<br><br>') : arg.content;
                            
                            let formattedContent = mainContent.replace(/\n/g, '<br>');
                            formattedContent = formattedContent.replace(/\*\*(.*?)\*\*/g, '<span class="text-warning font-black bg-warning/10 px-1 rounded">$1</span>');

                            const likeHtml = `<button id="like-btn-${arg.id}" onclick="toggleLike(${arg.id})" class="btn btn-xs mt-2 ${arg.is_liked ? 'btn-error shadow-lg' : 'btn-outline border-gray-600 text-gray-400'}">❤️ ${arg.likes_count}</button>`;

                            debateBox.innerHTML += `
                                <div class="chat ${bubbleLayout} drop-shadow-xl w-full mb-6 flex flex-col ${isPro ? 'items-start' : 'items-end'}">
                                    <div class="chat-header mb-2 flex items-center gap-2">
                                        <span class="badge ${badgeColor} badge-sm font-black text-white shadow-lg px-3 py-3">${stanceLabel}</span>
                                        <span class="text-xs md:text-sm text-gray-300 font-bold uppercase tracking-wider">${aiNameRaw}</span>
                                    </div>
                                    <div class="chat-bubble ${bubbleColor} text-gray-100 p-5 md:p-6 text-base md:text-lg border-l-4 border-r-4 ${borderColor} rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.3)] max-w-[95%] md:max-w-[85%] leading-relaxed backdrop-blur-sm">
                                        ${formattedContent}
                                    </div>
                                    ${likeHtml}
                                </div>
                            `;
                        });
                        
                        if(!forceUpdateLikes) {
                            debateBox.scrollTo({ top: debateBox.scrollHeight, behavior: 'smooth' });
                        }
                        lastArgumentCount = data.length;
                    }
                });
            }

            const chatForm = document.getElementById('chat-form');
            const chatInput = document.getElementById('chat-input');
            
            window.deleteComment = function(commentId) {
                if(confirm('Hapus komentar ini?')) {
                    fetch(`/arena/${roomId}/comment/${commentId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    }).then(() => fetchComments());
                }
            };

            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const text = chatInput.value.trim();
                if(!text) return;

                fetch(`/arena/${roomId}/comment`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ content: text })
                }).then(() => {
                    chatInput.value = '';
                    fetchComments();
                });
            });

            function fetchComments() {
                fetch(`/arena/${roomId}/comments`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.json())
                .then(data => {
                    let chatHTML = '';
                    data.forEach(c => {
                        let roleBadge = '';
                        if(c.user_role === 'prompter') roleBadge = '<span class="text-[10px] bg-warning text-black px-1 rounded font-bold ml-1">PROMPTER</span>';
                        else if(c.user_role === 'moderator') roleBadge = '<span class="text-[10px] bg-secondary text-white px-1 rounded font-bold ml-1">MOD</span>';
                        
                        let deleteBtn = '';
                        if(myRole === 'prompter' || myRole === 'moderator') {
                            deleteBtn = `<button onclick="deleteComment(${c.id})" class="text-error hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity ml-auto" title="Hapus">🗑️</button>`;
                        }

                        chatHTML += `
                            <div class="group text-sm leading-tight border-b border-gray-700/50 pb-2 flex">
                                <div class="flex-1">
                                    <span class="font-bold text-gray-300">${c.user_name}</span>${roleBadge}: 
                                    <span class="text-gray-400 break-words">${c.content}</span>
                                </div>
                                ${deleteBtn}
                            </div>
                        `;
                    });
                    
                    const isScrolledToBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 10;
                    chatBox.innerHTML = chatHTML;
                    if(isScrolledToBottom) {
                        chatBox.scrollTop = chatBox.scrollHeight;
                    }
                });
            }

            setInterval(fetchArguments, 3000);
            setInterval(fetchComments, 3000);
            
            fetchArguments();
            fetchComments();
        });
    </script>
</x-app-layout>