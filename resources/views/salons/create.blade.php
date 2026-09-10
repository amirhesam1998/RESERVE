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

                <div class="mb-4">
                    <label class="block mb-2 font-medium">
                        SESSIONS
                    </label>

                    @foreach ($sessions as $session)
                        <label
                            class="flex items-center space-x-3 bg-gray-50 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="sessions[]" value="{{ $session->id }}"
                                class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                {{ is_array(old('sessions')) && in_array($session->id, old('sessions')) ? 'checked' : '' }}>
                            <span class="text-gray-700 font-medium">
                                {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium">
                        Primary Image (cover photo)
                    </label>
                    <input type="file" id="salon-primary-image-input" name="primary_image" accept="image/*"
                        class="w-full border rounded-lg p-3">
                    <div id="primary-image-preview" class="mt-3"></div>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium">
                        Salon Images
                    </label>
                    <input type="file" id="salon-images-input" name="images[]" multiple accept="image/*"
                        class="w-full border rounded-lg p-3">
                    <p class="text-sm text-gray-500 mt-1">You can select multiple images.</p>

                    <div id="image-preview-container" class="grid grid-cols-3 gap-3 mt-4"></div>
                </div>

                <x-input label="address" name="address" class="mb-4" value="{{ old('address') }}">
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

        let selectedImageFiles = [];

        const imagesInput = document.getElementById('salon-images-input');
        const previewContainer = document.getElementById('image-preview-container');

        imagesInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            selectedImageFiles = selectedImageFiles.concat(newFiles);

            syncInputFiles();
            renderPreviews();
        });

        function renderPreviews() {
            previewContainer.innerHTML = '';

            selectedImageFiles.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(event) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative';

                    wrapper.innerHTML = `
                        <img src="${event.target.result}"
                             class="w-full h-32 object-cover rounded-lg border border-gray-200">
                        <button type="button"
                                data-index="${index}"
                                class="remove-preview-btn absolute top-1 left-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-700">
                            ✕
                        </button>
                    `;

                    previewContainer.appendChild(wrapper);
                };

                reader.readAsDataURL(file);
            });
        }

        previewContainer.addEventListener('click', function(e) {
            if (!e.target.classList.contains('remove-preview-btn')) return;

            const index = parseInt(e.target.dataset.index, 10);
            selectedImageFiles.splice(index, 1);

            syncInputFiles();
            renderPreviews();
        });

        function syncInputFiles() {
            const dataTransfer = new DataTransfer();

            selectedImageFiles.forEach(file => {
                dataTransfer.items.add(file);
            });

            imagesInput.files = dataTransfer.files;
        }

        let selectedPrimaryImage = null;

        const primaryImageInput = document.getElementById('salon-primary-image-input');
        const primaryImagePreview = document.getElementById('primary-image-preview');

        primaryImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            selectedPrimaryImage = file || null;

            primaryImagePreview.innerHTML = '';

            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                primaryImagePreview.innerHTML = `
                    <div class="relative inline-block">
                        <img src="${event.target.result}"
                             class="w-40 h-40 object-cover rounded-lg border-2 border-blue-500">
                        <button type="button" id="remove-primary-btn"
                                class="absolute top-1 left-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-700">
                            ✕
                        </button>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        });

        primaryImagePreview.addEventListener('click', function(e) {
            if (e.target.id !== 'remove-primary-btn') return;

            selectedPrimaryImage = null;
            primaryImageInput.value = '';
            primaryImagePreview.innerHTML = '';
        });

        document.getElementById('demo-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            // 1. Get main category (ensure integer)
            const selectVal = document.getElementById('main-category').value;
            const mainCategory = selectVal ? parseInt(selectVal, 10) : null;

            // 2. Get checked children (filter out main category ID if accidentally selected twice)
            const checkedChildren = [...document.querySelectorAll('.child-category:checked')]
                .map(cb => parseInt(cb.value, 10))
                .filter(id => id !== mainCategory && !isNaN(id));

            const checkedSessions = [...document.querySelectorAll('input[name="sessions[]"]:checked')]
                .map(cb => parseInt(cb.value, 10));

            // 3. Construct JSON payload
            const data = {
                name: document.querySelector('[name="name"]').value.trim(),
                address: document.querySelector('[name="address"]').value.trim(),
                main_category: mainCategory,
                child_categories: checkedChildren,
                sessions: checkedSessions,
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

                if (selectedImageFiles.length > 0 || selectedPrimaryImage) {
                    await uploadSalonImages(result.salon_id, selectedImageFiles, selectedPrimaryImage);
                }

                window.location.href = `${window.APP_CONFIG.frontendUrl}/salons/${result.salon_id}/layout`;

            } catch (error) {
                console.error('Network Error:', error);
            }
        });

        async function uploadSalonImages(salonId, galleryFiles, primaryFile) {
            const formData = new FormData();

            if (primaryFile) {
                formData.append('primary_image', primaryFile);
            }

            for (const file of galleryFiles) {
                formData.append('images[]', file);
            }

            try {
                const response = await fetch(`/api/salon/${salonId}/images`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const errorResult = await response.json();
                    console.error('Image upload failed:', errorResult);
                    alert('Salon created, but image upload failed: ' + (errorResult.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Image upload network error:', error);
                alert('Salon created, but image upload failed due to a network error.');
            }
        }
    </script>




</x-layout>
