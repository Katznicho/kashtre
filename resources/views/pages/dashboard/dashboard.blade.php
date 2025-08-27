<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-[#011478]/10 to-transparent">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Impersonation Banner -->
            @impersonating
                <div
                    class="bg-yellow-500/80 backdrop-blur-sm text-white p-4 text-center flex justify-between items-center mx-auto max-w-7xl sm:px-6 lg:px-8 rounded-xl shadow-sm mb-4">
                    <span class="font-medium text-lg">
                        You are currently impersonating <strong>{{ auth()->user()->name }}</strong>.
                    </span>
                    <a href="{{ route('impersonate.leave') }}"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-xl text-sm font-semibold transition duration-200">
                        Stop Impersonating
                    </a>
                </div>
            @endImpersonating

            <!-- Page Title with User & Branch Info -->
            <div class="mb-8 flex justify-between items-center bg-white/50 backdrop-blur-sm p-6 rounded-xl shadow-sm">
                <div>
                    <h2 class="text-3xl font-bold text-[#011478]">Welcome to {{ $business->name ?? 'N/A' }}</h2>
                    <div class="flex items-center mt-2 space-x-4">
                        <p class="text-gray-600 font-medium">User: {{ Auth::user()->name }}</p>

                        @if ($currentBranch)
                            <span class="text-gray-400">|</span>
                            <p class="text-gray-600 font-medium">
                                Branch: {{ $currentBranch->name }}
                                @if(count($allowedBranches) > 1)
                                    <button onclick="showBranchSelection()" class="ml-2 text-blue-600 hover:text-blue-800 text-sm underline">
                                        (Change)
                                    </button>
                                @endif
                            </p>
                        @endif

                        <span class="text-gray-400">|</span>
                        <p class="text-gray-600 font-medium">{{ now()->format('l, F j, Y') }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-[#011478]/80">Current Time</p>
                    <p class="text-lg font-mono">{{ now()->format('H:i:s') }} UTC</p>
                </div>
            </div>

            <!-- Service Points Quick Access - Moved up for prominence -->
            @if(auth()->user()->service_points)
                <div class="mb-6 bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Service Points Dashboard</h3>
                                <p class="text-sm text-gray-600">Manage your service points and client queues</p>
                            </div>
                        </div>
                        <a href="{{ route('service-queues.index') }}" 
                           class="bg-[#011478] hover:bg-[#011478]/90 text-white px-6 py-3 rounded-lg font-semibold transition duration-200 flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <span>View Service Points</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Main Content -->
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-sm p-6">
                <!-- Dashboard Content -->
                @livewire('dashboard')
            </div>
        </div>
    </div>

    {{-- Hidden form to submit selected room --}}
    <form method="POST" action="{{ url()->current() }}" id="room-select-form" style="display:none;">
        @csrf
        <input type="hidden" name="room_id" id="selected-room-id" value="">
    </form>

    <script>
        window.userHasRoom = @json(session()->has('room_id'));
        window.rooms = @json($rooms);
        window.allowedBranches = @json($allowedBranches);
        window.currentBranchId = @json($currentBranch ? $currentBranch->id : null);

        document.addEventListener('DOMContentLoaded', function() {
            // If user doesn't have a room and there are rooms available
            if (!window.userHasRoom && window.rooms && window.rooms.length > 0) {
                // If there's only one room, auto-select it
                if (window.rooms.length === 1) {
                    const roomId = window.rooms[0].id;
                    fetch('{{ route('room.select') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            room_id: roomId
                        })
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        // Auto-select successful, reload page
                        location.reload();
                    })
                    .catch(() => {
                        console.log('Auto-room selection failed, continuing without room');
                    });
                } else {
                    // Multiple rooms available, show selection dialog
                    let optionsHtml = `<option value="" disabled selected>-- Select a Room --</option>` +
                        window.rooms.map(room =>
                            `<option value="${room.id}">${room.name}</option>`
                        ).join('');

                    Swal.fire({
                        title: 'Select Your Room',
                        html: `
                        <select id="swal-room-select" style="
                            width: 100%;
                            padding: 0.5rem 0.75rem;
                            font-size: 1rem;
                            border-radius: 0.375rem;
                            border: 1px solid #CBD5E1; /* Tailwind slate-300 */
                            color: #475569; /* Tailwind slate-600 */
                            background-color: white;
                        ">
                            ${optionsHtml}
                        </select>
                    `,
                        icon: 'info',
                        confirmButtonText: 'Confirm',
                        confirmButtonColor: '#A5B4FC', // Tailwind indigo-300
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        focusConfirm: false,
                        preConfirm: () => {
                            const selectedRoom = Swal.getPopup().querySelector('#swal-room-select').value;
                            if (!selectedRoom) {
                                Swal.showValidationMessage('Please select a room');
                            }
                            return selectedRoom;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const roomId = result.value;

                            fetch('{{ route('room.select') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({
                                        room_id: roomId
                                    })
                                })
                                .then(response => {
                                    if (!response.ok) throw new Error('Network response was not ok');
                                    return response.json();
                                })
                                .then(data => {
                                    Swal.fire({
                                        title: 'Success',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        location.reload();
                                    });
                                })
                                .catch(() => {
                                    Swal.fire('Error', 'Failed to set room. Please try again.', 'error');
                                });
                        }
                    });
                }
            }
            // If no rooms available, continue without room selection
        });

        function showBranchSelection() {
            if (!window.allowedBranches || window.allowedBranches.length === 0) {
                Swal.fire('No Branches Available', 'You do not have access to any branches.', 'info');
                return;
            }

            let optionsHtml = `<option value="" disabled>-- Select a Branch --</option>` +
                window.allowedBranches.map(branch =>
                    `<option value="${branch.id}" ${branch.id == window.currentBranchId ? 'selected' : ''}>${branch.name}</option>`
                ).join('');

            Swal.fire({
                title: 'Select Your Working Branch',
                html: `
                <select id="swal-branch-select" style="
                    width: 100%;
                    padding: 0.5rem 0.75rem;
                    font-size: 1rem;
                    border-radius: 0.375rem;
                    border: 1px solid #CBD5E1; /* Tailwind slate-300 */
                    color: #475569; /* Tailwind slate-600 */
                    background-color: white;
                ">
                    ${optionsHtml}
                </select>
            `,
                icon: 'info',
                confirmButtonText: 'Confirm',
                confirmButtonColor: '#A5B4FC', // Tailwind indigo-300
                allowOutsideClick: true,
                allowEscapeKey: true,
                focusConfirm: false,
                preConfirm: () => {
                    const selectedBranch = Swal.getPopup().querySelector('#swal-branch-select').value;
                    if (!selectedBranch) {
                        Swal.showValidationMessage('Please select a branch');
                    }
                    return selectedBranch;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const branchId = result.value;

                    fetch('{{ route('branch.select') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                branch_id: branchId
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            Swal.fire({
                                title: 'Success',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'Failed to set branch. Please try again.', 'error');
                        });
                }
            });
        }
    </script>



</x-app-layout>
