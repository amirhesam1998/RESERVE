<x-layout title="Login User">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">Login</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form id="loginForm" method="post">

                <x-input label="email" name="email" id="email" class="mb-4"></x-input>
                @error('email')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror

                <div class="mb-5">
                    <label class="block mb-2 font-medium"> password </label>
                    <x-input name="password" id="password" type="password"
                        class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">
                    </x-input>
                    @error('password')
                        <span class="bg-blue-600 font-bold">{{ $message }} </span> <br />
                    @enderror

                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition mb-4">
                        Login
                    </button>

                    <p class="text-center text-gray-600">
                        Don't have an account?
                        <a href="{{ route('user.create') }}"
                            class="text-blue-600 font-semibold hover:text-blue-800 hover:underline transition">
                            Sign up
                        </a>
                    </p>
                </div>
            </form>

            <script>
                document.getElementById('loginForm').addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const response = await fetch('/api/login', {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'include',
                        body: JSON.stringify({
                            email: document.getElementById('email').value,
                            password: document.getElementById('password').value
                        })
                    })
                    const data = await response.json();
                    console.log(data);

                    if (response.ok) {
                        console.log("Redirecting...");
                        window.location.href = `/users/${data.user.id}`;
                    } else {
                        alert(data.message);
                    }
                })
            </script>
        </div>
    </main>
</x-layout>
