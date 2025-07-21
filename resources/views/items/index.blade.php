<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Item Management') }}
        </h2>
    </x-slot>

    @livewire('item-manager')
</x-app-layout>
