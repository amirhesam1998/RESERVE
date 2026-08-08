<x-layout titile='categories'>

    <ul>

        @foreach ($categories as $category)
            @include('categories._category', ['category' => $category])
        @endforeach

    </ul>


</x-layout>
