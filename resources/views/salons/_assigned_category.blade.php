<li class="ml-4 mt-2">
    {{ $category->name }}

    @php
        $children = $salon->categories->where('parent_id', $category->id);
    @endphp

    @if ($children->isNotEmpty())

        <ul class="ml-6 border-l-2 border-gray-300 pl-4">
            @foreach ($children as $child)
                @include('salons._assigned_category', ['salon' => $salon, 'category' => $child])
            @endforeach
        </ul>
    @endif

</li>
