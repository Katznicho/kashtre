@foreach ($app_permissions as $group => $categories)
    @foreach ($categories as $category => $perms)
        <fieldset class="mb-6">
            <legend class="text-lg font-semibold mb-2">{{ $category }}</legend>
            <div class="flex flex-col space-y-1">
                @foreach ($perms as $permission)
                    <label class="inline-flex items-center space-x-2">
                        <input type="checkbox" name="permissions[]" value="{{ $category }}:{{ $permission }}">
                        <span>{{ $permission }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endforeach
@endforeach
