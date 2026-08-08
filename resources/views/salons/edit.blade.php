<x-layout title="Edit Info">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">edit Salon</h2>

            <form action="{{ route('salons.update', $salon->id) }}" method="post">
                @csrf
                @method('put')
                <div class="mb-5"></div>

                <x-input label="salon name" name="name" class="mb-4"
                    value="{{ old('name', $salon->name) }}"></x-input>
                @error('name')
                    <span class="bg-blue-600 font-bold">{{ $message }}</span>
                @enderror

                <x-input label="salon address" name="address" class="mb-4"
                    value="{{ old('address', $salon->address) }}"></x-input>
                @error('address')
                    <span class="bg-blue-600 font-bold">{{ $message }}</span>
                @enderror

                <select name="main_category" id="main-category">
                    {{--                         @foreach ($mainCategories as $category)
                            <option value="{{ $category->id }}" @selected(optional($mainCategory)->id == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach --}}
                </select>

                <div id="children-container">
                    {{--                         @foreach ($mainCategory->children as $child)
                            <label>
                                <input type="checkbox" name="child_categories[]" value="{{ $child->id }}"
                                    @checked(in_array($child->id, $assignedChildCategories))>
                                {{ $child->name }}
                            </label>
                        @endforeach --}}
                </div>


                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    Save and Continue
                </button>

            </form>
        </div>

    </main>

    <script>
        let categories = [];

        const selectedMainCategory = {{ $mainCategory->id }};
        const assignedChildren = @json($assignedChildCategories);

        const select = document.getElementById('main-category');
        const childrenContainer = document.getElementById('children-container');

        window.addEventListener('DOMContentLoaded', async () => {

            /* const response = await fetch('api/categories/tree');
             categories = await response.json();

             // Fill dropdown
             categories.forEach(category => {
                 appendCategory(category);
             });

             // Select current main category
             select.value = selectedMainCategory;

             // Draw children
             const current = findCategory(selectedMainCategory, categories);

             childrenContainer.innerHTML = '';

             appendChildren(current); */


            try {

                const response = await fetch('/api/categories/tree');

                console.log(response.status);

                const data = await response.json();

                console.log(data);

                categories = data;

                categories.forEach(category => appendCategory(category));

                select.value = selectedMainCategory;

                const current = findCategory(selectedMainCategory, categories);

                console.log(current);

                if (current) {
                    appendChildren(current);
                }

            } catch (e) {
                console.error(e);
            }

        });


        function appendCategory(category) {
            const option = document.createElement('option');

            option.value = category.id;
            option.textContent = category.name;

            select.appendChild(option);
        }


        function findCategory(id, categories) {
            for (const category of categories) {
                if (category.id == id)
                    return category;

                const found = findCategory(id, category.children);

                if (found)
                    return found;
            }

            return null;
        }


        function appendChildren(category, depth = 0) {
            category.children.forEach(child => {

                childrenContainer.insertAdjacentHTML(
                    'beforeend',

                    `
                <div
                    style="margin-left:${depth*20}px"
                    class="border-l border-gray-300 pl-4 mb-2">

                    <label class="flex items-center gap-2">

                        <input
                            type="checkbox"
                            class="child-category"
                            name="child_categories[]"
                            value="${child.id}"
                            data-parent="${category.id}"
                            ${assignedChildren.includes(child.id) ? 'checked' : ''}

                        >

                        ${child.name}

                    </label>

                </div>
                `
                );

                appendChildren(child, depth + 1);

            });
        }


        select.addEventListener('change', function() {

            childrenContainer.innerHTML = '';

            const category = findCategory(this.value, categories);

            if (category)
                appendChildren(category);

        });


        document.addEventListener('change', function(e) {

            if (!e.target.classList.contains('child-category'))
                return;

            // -----------------------
            // CHECKING
            // -----------------------
            if (e.target.checked) {

                let parentId = e.target.dataset.parent;

                while (parentId) {

                    const parentCheckbox = document.querySelector(
                        `.child-category[value="${parentId}"]`
                    );

                    if (!parentCheckbox)
                        break;

                    parentCheckbox.checked = true;

                    parentId = parentCheckbox.dataset.parent;
                }

            }
            // -----------------------
            // UNCHECKING
            // -----------------------
            else {

                uncheckChildren(e.target.value);

            }

        });

        function uncheckChildren(parentId) {

            const children = document.querySelectorAll(
                `.child-category[data-parent="${parentId}"]`
            );

            children.forEach(child => {

                child.checked = false;

                uncheckChildren(child.value);

            });

        }
    </script>

</x-layout>
