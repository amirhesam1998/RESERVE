<x-layout titile='salons'>
    @foreach ($salons as $salon)
        <div class="bg-white p-6 rounded-lg shadow">

            @if ($salon->image)
                <img src="{{ asset('storage/' . $salon->image) }}" alt="{{ $salon->name }}"
                    class="w-full h-56 object-cover rounded-lg mb-4">
            @endif

            <a href="#" class="salon-link text-2xl font-semibold text-blue-600" data-id= "{{ $salon->id }}">
                <h3>{{ $salon->name }}</h3>
            </a>

            <p class="text-gray-600 mb-4"> {{ $salon->address }} </p>

            <ul class="mt-4">
                @foreach ($salon->categories->where('pivot.is_main', true) as $category)
                    @include('salons._assigned_category', ['category' => $category, 'salon' => $salon])
                @endforeach
            </ul>

            <button class="mt-4 text-blue-600 hover:underline" action="posts/create">
                Read More
            </button>

            @can('salons', ['edit-salons'])
                <a href={{ route('salons.edit', $salon->id) }}
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    Edit
                    salon information </a>
            @endcan

            @can('salons', ['delete-salon'])
                <form action="{{ route('salons.destroy', $salon->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="mt-4 text-blue-600 hover:underline">
                        Delete Salon
                    </button>
                </form>
            @endcan


        </div>
    @endforeach


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

                    // This is where your frontend developer
                    // will receive the complete salon object.
                    // For now you're just testing:
                    window.location.href = `/salons/${salonId}/layout`;
                } catch (error) {

                    console.error(error);

                }

            });

        });
    </script>
</x-layout>
