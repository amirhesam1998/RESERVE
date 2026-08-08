<li class="border border-gray-300 rounded-lg p-3 mb-3">
    <div class="bg-white p-6 rounded-lg shadow">
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

    <a href="{{ route('category.show', $category->id) }}"> {{ $category->name }} </a>


    @if ($category->children->isNotEmpty())

        <ul class="ml-8 mt-3 border-l-2 border-gray-300 pl-4">
            @foreach ($category->children as $child)
                @include('categories._category', ['category' => $child])
            @endforeach
        </ul>

    @endif
</li>
