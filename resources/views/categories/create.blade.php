<x-layout title="create category">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">create category</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form action="{{ route('category.store') }}" method="post">
                @csrf
                <div class="mb-5">
                    <x-input label="category name" name="name" class="w-full border rounded p-2"
                        value="{{ old('name') }}">
                    </x-input>
                    @error('name')
                        <span class="bg-blue-600 font-bold">{{ $message }} </span>
                    @enderror
                </div class="mb-4">

                <div class='mb-4'>
                    <label class="block mb-2"> Parent Category </label>
                    <select name="parent_id" class="w-full border rounded p-2">
                        <option value="">NONE (main category) </option>

                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                {{--                <hr class="my-6 border-gray-200">

                <h3 class="text-lg font-semibold mb-4 text-gray-700">Assign System Permissions</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    @foreach ($permissions as $permission)
                        <label
                            class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{ is_array(old('permissions')) && in_array($permission->id, old('permissions')) ? 'checked' : '' }}>
                            <span class="text-gray-700 font-medium capitalize">
                                {{ str_replace('-', ' ', $permission->name) }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('pemissions')
                    <span class="text-red-500 text-sm font-bold block mb-4">{{ $message }}</span>
                @enderror --}}

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition"
                    data-callback='onSubmit' data-action='submit'>
                    create category
                </button>

            </form>
        </div>
    </main>
</x-layout>
