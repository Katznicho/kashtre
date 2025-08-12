<div>
    <!-- Branch Filter -->
    @if($availableBranches->count() > 1)
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Branch Filter</h3>
                <p class="text-sm text-gray-600 mt-1">Select a branch to view its clients</p>
            </div>
            <div class="flex items-center space-x-4">
                <select wire:model.live="selectedBranchId" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                    @foreach($availableBranches as $branch)
                        <option value="{{ $branch->id }}">
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endif

    <!-- Filament Table -->
    {{ $this->table }}
</div>
