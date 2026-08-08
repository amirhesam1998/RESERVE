<x-layout title="Edit Info">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">edit role</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form action="{{ route('roles.update', $role->id) }}" method="post">
                @csrf
                @method('put')
                <div class="mb-5"></div>
                <x-input label="role name" name="name" class="mb-4" value="{{ old('name', $role->name) }}">
                </x-input>
                @error('name')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror

                @foreach ($permissions as $permission)
                    <label
                        class="mb-5 flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            class="h-5 w-5 text-blue-600 rounded border-gray-300"
                            {{ is_array(old('permissions') && in_array($permission->id, old('permissions'))) || $role->permissions->contains($permission->id) ? 'checked' : '' }}>

                        <span class="text-gray-700 font-medium">{{ $permission->name }}</span>
                    </label>
                @endforeach

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    Edit role
                </button>

            </form>
        </div>
    </main>

</x-layout>
