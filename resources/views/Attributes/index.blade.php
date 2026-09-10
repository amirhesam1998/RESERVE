<x-layout titile='Attributes'>
    @foreach ($attributes as $attribute)
        <div class="bg-white p-6 rounded-lg shadow">
            <a>
                <h3 class="text-2xl font-semibold mb-2">{{ $attribute->name }}</h3>
            </a>

            <ul>
                @foreach ($attribute->attributeValues as $values)
                    <li>{{ $values->value }}</li>
                @endforeach
            </ul>


            <button class="mt-4 text-blue-600 hover:underline" action="posts/create">
                Read More
            </button>

            @can('attributes', ['update-attributes'])
                <a href="{{ route('attributes.edit', $attribute->id) }}"
                    class="bg-white text-blue-600 px-4 py-2 rounded-lg hover:bg-gray-100">
                    Edit Information
                </a>
            @endcan


            @can('attributes', ['delete-attributes'])
                <form action="{{ route('attributes.delete', $attribute->id) }}" method="post">
                    @csrf
                    @method('delete')
                    <button type="submit" class="mt-4 text-blue-600 hover:underline">
                        Delete Attribute
                    </button>
                </form>
            @endcan

        </div>
    @endforeach

</x-layout>
