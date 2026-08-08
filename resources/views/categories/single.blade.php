<x-layout titile="single role">
    {{--     <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-2xl font-semibold mb-2">{{ $category->name }}</h3>

        <button class="mt-4 text-blue-600 hover:underline" action="posts/create">
            Read More
        </button>

             @foreach ($role->permissions->pluck('name') as $permission_name)
            <li>{{ $permission_name }}</li>
        @endforeach 

        @can('categories', ['edit-categories'])
            <a href="{{ route('category.edit', $category->id) }}"
                class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                Edit category
            </a>
        @endcan

        @can('categories', ['delete-categories'])
            <form action="{{ route('category.destroy', $category->id) }}" method="post">
                @csrf
                @method('delete')
                <button type="submit" class="mt-4 text-blue-600 hover:underline">
                    Delete Category
                </button>
            </form>
        @endcan

    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif --}}



    <ul >

        @include('categories.category_single', ['category' => $category])

    </ul>

</x-layout>
