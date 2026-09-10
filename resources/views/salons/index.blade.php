<x-layout titile='salons'>
    <div class="container mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold mb-8">Salons</h1>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($salons as $salon)
                <div class="bg-white rounded-lg shadow overflow-hidden flex flex-col">

                    @if ($salon->primaryImage)
                        <img src="{{ $salon->primaryImage->url }}" alt="{{ $salon->name }}"
                            class="w-full h-48 object-cover">
                    @else
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                            No Image
                        </div>
                    @endif

                    <div class="p-5 flex flex-col flex-grow">
                        <a href="#" class="salon-link text-xl font-semibold text-blue-600 hover:underline mb-1"
                            data-id="{{ $salon->id }}">
                            {{ $salon->name }}
                        </a>

                        <p class="text-gray-500 text-sm mb-3">{{ $salon->address }}</p>

                        <div class="flex flex-wrap gap-2 mb-3">
                            @foreach ($salon->categories->where('pivot.is_main', true) as $category)
                                @include('salons._assigned_category', [
                                    'category' => $category,
                                    'salon' => $salon,
                                ])
                            @endforeach
                        </div>

                        <ul class="text-sm text-gray-600 mb-4 space-y-1">
                            @foreach ($salon->showTimes as $showtime)
                                <li class="inline-block bg-gray-100 rounded px-2 py-1 mr-1 mb-1">
                                    {{ $showtime->start_time->format('H:i') }} -
                                    {{ $showtime->end_time->format('H:i') }}
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-auto pt-4 border-t border-gray-100 flex flex-wrap items-center gap-3">
                            <button class="text-blue-600 hover:underline text-sm">
                                Read More
                            </button>

                            @can('salons', ['edit-salon'])
                                <a href="{{ route('salons.edit', $salon->id) }}"
                                    class="text-sm bg-gray-100 text-blue-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition">
                                    Edit
                                </a>
                            @endcan

                            @can('salons', ['delete-salon'])
                                <form action="{{ route('salons.destroy', $salon->id) }}" method="post"
                                    onsubmit="return confirm('Are you sure you want to delete this salon?');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="text-sm text-red-500 hover:underline">
                                        Delete
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div id="salon-display-area" class="mt-8 p-6 bg-gray-50 border rounded-lg"></div>
    </div>

    <script>
        window.APP_CONFIG = {
            backendUrl: "{{ rtrim(config('app.url'), '/') }}",
            frontendUrl: "{{ rtrim(config('app.frontend_url'), '/') }}",
        };
    </script>

    <script>
        document.querySelectorAll('.salon-link').forEach(link => {
            link.addEventListener('click', async function(e) {
                e.preventDefault();
                const salonId = this.dataset.id;

                try {
                    const response = await fetch(`/api/salon/${salonId}`, {
                        credentials: 'include',
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load salon.');
                    }

                    const result = await response.json();
                    console.log(result.data);

                    window.location.href = `${window.APP_CONFIG.frontendUrl}/salons/${salonId}/layout`;
                } catch (error) {
                    console.error(error);
                }
            });
        });
    </script>
</x-layout>
