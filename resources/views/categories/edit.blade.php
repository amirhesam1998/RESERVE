<x-layout title="Edit category">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">edit category</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form action="{{ route('category.update', $category->id) }}" method="post">
                @csrf
                @method('put')
                <div class="mb-5"></div>
                <x-input label="category name" name="name" class="mb-4" value="{{ old('name', $category->name) }}">
                </x-input>
                @error('name')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror

                <div class="mb-4">
                    <label class="block mb-2">Parent Category</label>
                    <select name="parent_id" class="w-full border rounded p-2">
                        <option value="">NONE (main category)</option>

                        @foreach ($categories as $cat)
                            <option value="{{ $cat['id'] }}"
                                {{ old('parent_id', $category->parent_id ?? '') == $cat['id'] ? 'selected' : '' }}>
                                {{ $cat['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    Edit category
                </button>

            </form>
        </div>
    </main>

</x-layout>
