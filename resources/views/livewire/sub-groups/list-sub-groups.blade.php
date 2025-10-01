@php
    \Log::info('livewire/sub-groups/list-sub-groups.blade.php view started rendering');
@endphp

<div>
    @php
        \Log::info('About to render table: ' . get_class($this->table));
    @endphp
    
    {{ $this->table }}
    
    @php
        \Log::info('Table rendered successfully');
    @endphp
</div>

@php
    \Log::info('livewire/sub-groups/list-sub-groups.blade.php view finished rendering');
@endphp
