<x-layout titile='sessions'>
    @foreach ($sessions as $session)
        <div class="bg-white p-6 rounded-lg shadow">
            <a href="{{ route('roles.show', $session->id) }}">
                <h3 class="text-2xl font-semibold mb-2">{{ $session->start_time?->format('H:i') }} --
                    {{ $session->end_time?->format('H:i') }}</h3>
            </a>

            <button class="mt-4 text-blue-600 hover:underline" action="posts/create">
                Read More
            </button>


            @can('sessions', ['edit-sessions'])
                <a href="{{ route('sessions.edit', $session->id) }}"
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    Edit Information
                </a>
            @endcan


            @can('sessions', ['delete-sessions'])
                <form action="{{ route('sessions.delete', $session->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="mt-4 text-blue-600 hover:underline">
                        Delete Session
                    </button>
                </form>
            @endcan
        </div>
    @endforeach
</x-layout>
