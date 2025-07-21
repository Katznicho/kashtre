<div>
    <!-- Main Content -->
    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <input type="text" wire:model.live="search" 
                            class="w-full px-4 py-2 border rounded-lg" 
                            placeholder="Search items...">
                    </div>
                    <!-- Actions (Add/Upload) -->
                    <div class="flex space-x-2 mb-4">
                        <button wire:click="create" 
                            class="px-4 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                            Add Item
                        </button>
                        <label class="px-4 py-2 text-white bg-green-500 rounded-lg hover:bg-green-600 cursor-pointer">
                            Upload File
                            <input type="file" wire:model="file" class="hidden" 
                                accept=".csv,.xlsx">
                        </label>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $item->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ number_format($item->price, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button wire:click="edit({{ $item->id }})" 
                                                class="text-blue-600 hover:text-blue-900 mr-2">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $item->id }})" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Are you sure?')">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                            No items found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $items->links() }}
                    </div>
                    <!-- Download Template Buttons -->
                    <div class="flex space-x-2 mt-6">
                        <button wire:click="downloadTemplate('xlsx')" 
                            class="px-4 py-2 text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                            Download Excel Template
                        </button>
                        <button wire:click="downloadTemplate('csv')" 
                            class="px-4 py-2 text-white bg-gray-500 rounded-lg hover:bg-gray-600">
                            Download CSV Template
                        </button>
                    </div>

                    <!-- Modal -->
                    @if($showModal)
                    <div class="fixed inset-0 z-10 overflow-y-auto">
                        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                            <div class="fixed inset-0 transition-opacity">
                                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                            </div>
                            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                                <form wire:submit.prevent="{{ $isEditing ? 'update' : 'store' }}">
                                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                                Name
                                            </label>
                                            <input type="text" wire:model="name" 
                                                class="w-full px-3 py-2 border rounded-lg">
                                            @error('name') 
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                                Price
                                            </label>
                                            <input type="number" wire:model="price" step="0.01"
                                                class="w-full px-3 py-2 border rounded-lg">
                                            @error('price') 
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                        <button type="submit" 
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            {{ $isEditing ? 'Update' : 'Save' }}
                                        </button>
                                        <button type="button" wire:click="$set('showModal', false)"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div x-data="{ show: false, message: '' }"
         x-on:item-saved.window="show = true; message = 'Item saved successfully!'; setTimeout(() => show = false, 3000)"
         x-on:item-updated.window="show = true; message = 'Item updated successfully!'; setTimeout(() => show = false, 3000)"
         x-on:item-deleted.window="show = true; message = 'Item deleted successfully!'; setTimeout(() => show = false, 3000)"
         x-on:upload-success.window="show = true; message = 'File uploaded successfully!'; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition
         class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <span x-text="message"></span>
    </div>
</div>
