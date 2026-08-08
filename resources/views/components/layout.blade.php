@props([
    'title' => 'no title',
    'createRoute' => null,
    'buttonText' => null,
])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ?? 'no titile' }}</title>
    @vite (["resources/css/app.css", "resources/js/app.js"])
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Header -->
    @include ('components.header')
    @if (session('error'))
        <div class="container mx-auto px-6 mt-4">
            <div class="rounded-lg bg-red-100 border border-red-400 px-4 py-3 text-red-700">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <!-- Main Content -->

    {{ $slot }}

    {{ $scripts ?? '' }}

    <!-- Footer -->
    @include ('components.footer')
</body>

</html>

</html>
