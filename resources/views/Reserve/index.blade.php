<x-layout titile='roles'>
    @foreach ($reserves as $reserve)
        <div class="bg-white p-6 rounded-lg shadow">
            <a href="{{ route('roles.show', $role->id) }}">
                <h3 class="text-2xl font-semibold mb-2">{{ $reserve->name }}</h3>
            </a>

            <button class="mt-4 text-blue-600 hover:underline" action="posts/create">
                Read More
            </button>
            {{-- 
            @can('roles', ['edit-roles'])
                <a href="{{ route('roles.edit', $role->id) }}"
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    Edit Information
                </a>
            @endcan

            @can('roles', ['delete-roles'])
                <form action="{{ route('roles.destroy', $role->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="mt-4 text-blue-600 hover:underline">
                        Delete Role
                    </button>
                </form>
            @endcan --}}

        </div>
    @endforeach
</x-layout>
