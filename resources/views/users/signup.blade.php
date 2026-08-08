<x-layout title="create user">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">Signup</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form action="{{ route('user.store') }}" method="post" id="demo-form">
                @csrf
                <div class="mb-5"></div>
                <x-input label="first name" name="first_name" class="mb-4" value="{{ old('first_name') }}">
                </x-input>
                @error('first_name')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror

                <x-input label="last name" name="last_name" class="mb-4" value="{{ old('last_name') }}">
                </x-input>
                @error('last_name')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror


                <x-input label="email" name="email" class="mb-4">{{ old('email') }}</x-input>
                @error('email')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror

                <x-input label="phone number" name="phone_number" type="tel" class="mb-4"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'')">{{ old('phone_number') }}</x-input>
                @error('phone_number')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span> <br />
                @enderror
                <div class="mb-5">
                    <label class="block mb-2 font-medium"> password </label>
                    <x-input name="password" type="password"
                        class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">
                    </x-input>
                    @error('password')
                        <span class="bg-blue-600 font-bold">{{ $message }} </span> <br />
                    @enderror

                    <x-input label="confirmation password" name="password_confirmation" type="password"
                        class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </x-input>
                </div>
                @error('password_confirmation')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span> <br />
                @enderror
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition g-recaptcha"
                    data-sitekey="{{ env('GOOGLE_RECAPTCHA_KEY') }}" data-callback='onSubmit' data-action='submit'>
                    create user
                </button>

            </form>
        </div>
    </main>
    <x-slot:scripts>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>

        <script>
            function onSubmit(token) {
                document.getElementById("demo-form").submit();
            }
        </script>

    </x-slot:scripts>



</x-layout>
