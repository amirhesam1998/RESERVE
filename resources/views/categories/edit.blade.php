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

                <label class="block mb-2"> Parent Category </label>

                <select name="parent_id" class="mb-4 w-full border rounded p-2">

                    @if ($parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                    @else
                        <option value="">NONE (main category)</option>
                    @endif


                    @foreach ($categories as $item)
                        <option value="{{ $item->id }}"> {{ $item->name }} </option>
                    @endforeach

                    <option value=""> NONE (main category) </option>

                </select>

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    Edit category
                </button>

            </form>
        </div>
    </main>

</x-layout>
