<div>
    <!-- Entity Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Entity Filter</h3>
                <p class="text-sm text-gray-600 mt-1">Select an entity type and specific entity to filter service charges</p>
            </div>
            <div class="flex items-center space-x-4">
                <select wire:model.live="selectedEntityType" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                    <option value="business">Business</option>
                    <option value="branch">Branch</option>
                    <option value="service_point">Service Point</option>
                </select>
                
                @if($availableEntities->isNotEmpty())
                <select wire:model.live="selectedEntityId" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#011478] focus:border-transparent">
                    @foreach($availableEntities as $entity)
                        <option value="{{ $entity->id }}">
                            {{ $entity->name ?? $entity->title ?? 'N/A' }}
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
