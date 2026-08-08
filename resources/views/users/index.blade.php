<x-layout titile='users'>
    @foreach ($users as $user)
        <div class="bg-white p-6 rounded-lg shadow">
            <h3 class="text-2xl font-semibold mb-2">{{ $user->first_name }} {{ $user->last_name }}</h3>
            <p class="text-gray-600 mb-4">{{ $user->email }}</p>
            <p class="text-gray-700">{{ $user->phone_number }}</p>

            @can('users', [$user, 'edit-clients'])
                <a href="{{ route('users.edit', $user->id) }}"
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    Edit Information
                </a>
            @endcan

            @can('users', [$user, 'editpass-clients'])
                <a href="{{ route('user.editpass.form', $user->id) }}"
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    Edit Password
                </a>
            @endcan

            <button class="mt-4 text-blue-600 hover:underline" action="posts/create">
                Read More
            </button>

            @can('users', [$user, 'delete-clients'])
                <form action="{{ route('users.destroy', $user->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" onclick="return confirm('Are you sure you want to delete this user?')"
                        class="mt-4 text-blue-600 hover:underline">
                        Delete Acount
                    </button>
                </form>
            @endcan
        </div>
    @endforeach
</x-layout>
