<x-layout title="create session">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">create session</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form action="{{ route('session.store') }}" method="post">
                @csrf
                <div class="mb-5"></div>
                <hr class="my-6 border-gray-200">

                <div class="mb-5">
                    <label class="block text-gray-700 font-medium mb-2">start time</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('start_time')
                        <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-5">
                    <label class="block text-gray-700 font-medium mb-2">end time</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('end_time')
                        <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <h3 class="text-lg font-semibold mb-4 text-gray-700">Assign salons</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                    @foreach ($salons as $salon)
                        <label
                            class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="salons[]" value="{{ $salon->id }}"
                                class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{ is_array(old('salons')) && in_array($salon->id, old('salons')) ? 'checked' : '' }}>
                            <span class="text-gray-700 font-medium capitalize">
                                {{ str_replace('-', ' ', $salon->name) }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('salons')
                    <span class="text-red-500 text-sm font-bold block mb-4">{{ $message }}</span>
                @enderror

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition"
                    data-callback='onSubmit' data-action='submit'>
                    create session
                </button>

            </form>
        </div>
    </main>
</x-layout>
