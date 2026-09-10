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

                <div class="mb-6">
                    <label class="block mb-2 font-medium">
                        Existing Images
                    </label>

                    <div id="existing-images-container" class="grid grid-cols-3 gap-3">
                        @foreach ($salon->images as $image)
                            <div class="relative existing-image-item" data-image-id="{{ $image->id }}">
                                <img src="{{ $image->url }}"
                                    class="w-full h-32 object-cover rounded-lg border-2 {{ $image->is_primary ? 'border-blue-500' : 'border-gray-200' }}">

                                @if ($image->is_primary)
                                    <span
                                        class="absolute top-1 right-1 bg-blue-600 text-white text-xs px-2 py-0.5 rounded">
                                        Primary
                                    </span>
                                @else
                                    <button type="button"
                                        class="set-primary-btn absolute top-1 right-1 bg-white text-blue-600 text-xs px-2 py-0.5 rounded border border-blue-500 hover:bg-blue-50">
                                        Set Primary
                                    </button>
                                @endif

                                <button type="button"
                                    class="delete-image-btn absolute top-1 left-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-700">
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @if ($salon->images->isEmpty())
                        <p class="text-gray-400 text-sm">No images uploaded yet.</p>
                    @endif
                </div>

                <div class="mb-6">
                    <label class="block mb-2 font-medium">
                        Add New Images
                    </label>
                    <input type="file" id="new-images-input" multiple accept="image/*"
                        class="w-full border rounded-lg p-3">
                    <div id="new-image-preview-container" class="grid grid-cols-3 gap-3 mt-3"></div>

                    <button type="button" onclick="uploadNewSalonImages()"
                        class="mt-3 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                        Upload Selected Images
                    </button>
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
                                {{ (is_array(old('sessions')) && in_array($session->id, old('sessions'))) || $salon->showTimes->contains($session->id) ? 'checked' : '' }}>
                            <span class="text-gray-700 font-medium">
                                {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
                            </span>
                        </label>
                    @endforeach
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

        const salonId = {{ $salon->id }};

        // حذف یه تصویر موجود
        document.getElementById('existing-images-container')?.addEventListener('click', async function(e) {
            if (!e.target.classList.contains('delete-image-btn')) return;

            if (!confirm('آیا مطمئن هستید می‌خواهید این تصویر را حذف کنید؟')) return;

            const item = e.target.closest('.existing-image-item');
            const imageId = item.dataset.imageId;

            try {
                const response = await fetch(`/api/images/${imageId}`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '',
                    },
                });

                if (!response.ok) {
                    const result = await response.json();
                    alert('خطا در حذف تصویر: ' + (result.message || 'خطای ناشناخته'));
                    return;
                }

                item.remove();
            } catch (error) {
                console.error(error);
                alert('خطا در ارتباط با سرور هنگام حذف تصویر.');
            }
        });

        // انتخاب یک تصویر به‌عنوان primary
        document.getElementById('existing-images-container')?.addEventListener('click', async function(e) {
            if (!e.target.classList.contains('set-primary-btn')) return;

            const item = e.target.closest('.existing-image-item');
            const imageId = item.dataset.imageId;

            try {
                const response = await fetch(`/api/images/${imageId}/primary`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '',
                    },
                });

                if (!response.ok) {
                    const result = await response.json();
                    alert('خطا در تنظیم تصویر اصلی: ' + (result.message || 'خطای ناشناخته'));
                    return;
                }

                // بعد از موفقیت، صفحه رو رفرش می‌کنیم تا وضعیت جدید primary درست نمایش داده بشه
                window.location.reload();
            } catch (error) {
                console.error(error);
                alert('خطا در ارتباط با سرور.');
            }
        });

        // پیش‌نمایش و مدیریت تصاویر جدیدی که کاربر می‌خواد اضافه کنه
        let selectedNewImages = [];

        const newImagesInput = document.getElementById('new-images-input');
        const newImagePreviewContainer = document.getElementById('new-image-preview-container');

        newImagesInput?.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            selectedNewImages = selectedNewImages.concat(newFiles);

            syncNewImagesInput();
            renderNewImagePreviews();
        });

        function renderNewImagePreviews() {
            newImagePreviewContainer.innerHTML = '';

            selectedNewImages.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(event) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative';

                    wrapper.innerHTML = `
                <img src="${event.target.result}"
                     class="w-full h-32 object-cover rounded-lg border border-gray-200">
                <button type="button"
                        data-index="${index}"
                        class="remove-new-preview-btn absolute top-1 left-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm hover:bg-red-700">
                    ✕
                </button>
            `;

                    newImagePreviewContainer.appendChild(wrapper);
                };

                reader.readAsDataURL(file);
            });
        }

        newImagePreviewContainer.addEventListener('click', function(e) {
            if (!e.target.classList.contains('remove-new-preview-btn')) return;

            const index = parseInt(e.target.dataset.index, 10);
            selectedNewImages.splice(index, 1);

            syncNewImagesInput();
            renderNewImagePreviews();
        });

        function syncNewImagesInput() {
            const dataTransfer = new DataTransfer();
            selectedNewImages.forEach(file => dataTransfer.items.add(file));
            newImagesInput.files = dataTransfer.files;
        }

        // آپلود تصاویر جدید به سالن (جدا از فرم اصلی، بلافاصله بعد از انتخاب)
        async function uploadNewSalonImages() {
            if (selectedNewImages.length === 0) return;

            const formData = new FormData();
            selectedNewImages.forEach(file => formData.append('images[]', file));

            try {
                const response = await fetch(`/api/salon/${salonId}/images`, {
                    method: 'POST',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content') || '',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    const result = await response.json();
                    alert('خطا در آپلود تصاویر: ' + (result.message || 'خطای ناشناخته'));
                    return;
                }

                window.location.reload();
            } catch (error) {
                console.error(error);
                alert('خطا در ارتباط با سرور هنگام آپلود تصاویر.');
            }
        }
    </script>

</x-layout>
