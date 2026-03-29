<div x-show="callState === 'incoming'"
     style="display: none;"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/90 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="opacity-0 scale-95"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-95">

    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-8 max-w-sm w-full text-center border border-gray-200 dark:border-slate-700">
        <!-- Avatar with pulsing ring -->
        <div class="relative w-24 h-24 mx-auto mb-6">
            <div class="absolute inset-0 rounded-full bg-indigo-500 opacity-20 animate-ping"></div>
            <img :src="remoteUser?.photo" class="w-24 h-24 rounded-full border-4 border-white dark:border-slate-800 relative z-10 object-cover shadow-lg" alt="CallerAvatar">
        </div>

        <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-100" x-text="remoteUser?.name"></h3>
        <p class="text-slate-500 dark:text-slate-400 mt-1 mb-8 animate-pulse">Incoming Audio Call...</p>

        <div class="flex justify-center gap-6">
            <button @click="rejectCall()" class="flex flex-col items-center group">
                <div class="w-14 h-14 rounded-full bg-rose-500 hover:bg-rose-600 flex items-center justify-center shadow-lg shadow-rose-500/30 transition-all group-hover:-translate-y-1">
                    <svg class="w-6 h-6 text-white transform rotate-135" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
                <span class="text-sm font-medium text-slate-600 dark:text-slate-400 mt-2">Decline</span>
            </button>
            <button @click="acceptCall()" class="flex flex-col items-center group">
                <div class="w-16 h-16 rounded-full bg-emerald-500 hover:bg-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/40 transition-all group-hover:-translate-y-1 animate-bounce">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-2">Accept</span>
            </button>
        </div>
    </div>
</div>
