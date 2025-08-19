<div>
    <!-- Business Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Business Filter</h3>
                <p class="text-sm text-gray-600 mt-1">Select a business to filter service charges</p>
            </div>
            <div class="flex items-center space-x-4">
                @if($availableBusinesses->isNotEmpty())
                <select wire:model.live="selectedBusinessId" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                    @foreach($availableBusinesses as $business)
                        <option value="{{ $business->id }}">
                            {{ $business->name }}
                        </option>
                    @endforeach
                </select>
                @endif
            </div>
        </div>
    </div>

    <!-- Filament Table -->
    {{ $this->table }}
</div>
