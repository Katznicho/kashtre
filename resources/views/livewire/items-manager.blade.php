<div>
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
<div class="max-w-2xl mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">Add New Item</h2>

    @if(isset($successMessage) && $successMessage)
        <div class="bg-green-100 text-green-800 p-2 rounded mb-2">
            {{ $successMessage }}
        </div>
    @endif

    <form wire:submit.prevent="addItem" class="mb-6">
        <div class="mb-2">
            <label for="name" class="block font-semibold">Name</label>
            <input type="text" id="name" wire:model="name" class="border rounded w-full p-2" required>
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-2">
            <label for="price" class="block font-semibold">Price</label>
            <input type="number" id="price" wire:model="price" class="border rounded w-full p-2" step="0.01" required>
            @error('price') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Add Item</button>
    </form>

    <h2 class="text-xl font-bold mb-4">Item List</h2>
    <table class="w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2 border">#</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Price</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td class="p-2 border">{{ $item->id }}</td>
                    <td class="p-2 border">{{ $item->name }}</td>
                    <td class="p-2 border">{{ number_format($item->price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="p-2 border text-center text-gray-500">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
