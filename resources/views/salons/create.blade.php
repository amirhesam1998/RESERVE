<x-layout title="create Salon">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">new Salon</h2>

            @php
                $titleField = 'mb-4 bg-red-600';
            @endphp

            <form id="demo-form">
                @csrf

                <x-input label="salon name" name="name" class="mb-4" value="{{ old('name') }}">
                </x-input>
                @error('name')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror

                <div class="mb-4">
                    <label class="block mb-2 font-medium">
                        Main Category
                    </label>

                    <select id="main-category" name="main_category" class="w-full border rounded-lg p-3">
                        <option value="">Select a category</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium">
                        Child Categories
                    </label>

                    <div id="children-categories">

                    </div>
                </div>

                <x-input label="address" name="address" class="mb-4" value="{{ old('name') }}">
                </x-input>
                @error('address')
                    <span class="bg-blue-600 font-bold">{{ $message }} </span>
                @enderror



                <div id="sections-container">

                </div>

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    Save & Continue
                </button>

            </form>
        </div>
    </main>

    <script>
        window.APP_CONFIG = {
            backendUrl: "{{ rtrim(config('app.url'), '/') }}",
            frontendUrl: "{{ rtrim(config('app.frontend_url'), '/') }}",
        };
    </script>

    <script>
        let categories = [];

        const select = document.getElementById('main-category');
        const childrenContainer = document.getElementById('children-categories');

        window.addEventListener('DOMContentLoaded', async () => {

            try {

                const response = await fetch('/api/categories/tree', {
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error('Could not load categories.');
                }

                categories = await response.json();

                categories.forEach(category => {
                    appendCategoryOption(category);
                });

            } catch (error) {
                console.error(error);
            }

        });

        /**
         * Add one category and all its children to the dropdown.
         */
        /* function appendCategory(category, depth = 0) {

            categories.forEach(category => {
                appendCategory(category);
            });

            function appendCategory(category) {

                const option = document.createElement('option');

                option.value = category.id;
                option.textContent = category.name;

                select.appendChild(option);

            }
        } */

        function appendCategoryOption(category, depth = 0) {
            const option = document.createElement('option');
            option.value = category.id;

            const prefix = depth > 0 ? '— '.repeat(depth) : '';
            option.textContent = prefix + category.name;

            select.appendChild(option);

            if (category.children && category.children.length > 0) {
                category.children.forEach(child => {
                    appendCategoryOption(child, depth + 1);
                });
            }
        }

        /**
         * Find a category recursively by id.
         */
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
                class="pl-4 border-l border-gray-300 mb-2"
                style="margin-left:${depth * 20}px">

                <label class="flex items-center gap-2">

                    <input
                        type="checkbox"
                        class="child-category"
                        name="child_categories[]"
                        value="${child.id}"
                        data-parent="${category.id}"
                    >

                    ${child.name}

                </label>

            </div>
            `
                );

                appendChildren(child, depth + 1);

            });

        }

        /**
         * When the user selects a category,
         * display all its direct children.
         */
        select.addEventListener('change', function() {

            childrenContainer.innerHTML = '';

            if (!this.value)
                return;

            const selected = findCategory(this.value, categories);

            if (!selected)
                return;

            if (selected.children.length === 0) {

                childrenContainer.innerHTML =
                    '<p class="text-gray-500 italic">هیچ دسته بندی ای وجود ندارد</p>';

                return;

            }

            appendChildren(selected);

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

        document.getElementById('demo-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            // 1. Get main category (ensure integer)
            const selectVal = document.getElementById('main-category').value;
            const mainCategory = selectVal ? parseInt(selectVal, 10) : null;

            // 2. Get checked children (filter out main category ID if accidentally selected twice)
            const checkedChildren = [...document.querySelectorAll('.child-category:checked')]
                .map(cb => parseInt(cb.value, 10))
                .filter(id => id !== mainCategory && !isNaN(id));

            // 3. Construct JSON payload
            const data = {
                name: document.querySelector('[name="name"]').value.trim(),
                address: document.querySelector('[name="address"]').value.trim(),
                main_category: mainCategory,
                child_categories: checkedChildren,
            };

            console.log("Sending Payload to API:", data);

            try {
                const response = await fetch('/api/salon/create', {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                    },
                    body: JSON.stringify(data),
                });

                const rawText = await response.text();

                let result;
                try {
                    result = JSON.parse(rawText);
                } catch (jsonError) {
                    console.error("Non-JSON Server Output:", rawText);
                    alert("Server Error! Check F12 Console.");
                    return;
                }

                if (!response.ok) {
                    console.error('Validation Error Details:', result);
                    alert(result.message || 'Validation failed. Check form inputs.');
                    return;
                }

                console.log(result);
                window.location.href = `${window.APP_CONFIG.frontendUrl}/salons/${result.salon_id}/layout`;

            } catch (error) {
                console.error('Network Error:', error);
            }
        });
    </script>




</x-layout>
