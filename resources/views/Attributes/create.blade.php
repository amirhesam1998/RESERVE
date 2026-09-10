<x-layout title="create Attribute">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">Create Attributes</h2>

            <form action="{{ route('attributes.store') }}" method="post" id="attributesForm">
                @csrf

                <div id="attributesWrapper"></div>

                <button type="button" id="addAttributeBtn"
                    class="w-full mb-6 border-2 border-dashed border-blue-400 text-blue-600 py-2 rounded-lg hover:bg-blue-50">
                    + افزودن Attribute جدید
                </button>

                @error('attributes')
                    <span class="text-red-600 font-bold block mb-4">{{ $message }}</span>
                @enderror

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    ذخیره
                </button>
            </form>
        </div>
    </main>

    <template id="attributeTemplate">
        <div class="attribute-block mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50 relative">
            <button type="button"
                class="remove-attribute-btn absolute top-2 left-2 text-red-500 hover:text-red-700 text-sm font-bold">
                حذف Attribute
            </button>

            <div class="mb-2">
                <label class="block mb-1 font-semibold text-gray-700">Attribute name</label>
                <input type="text" class="attribute-name-input w-full border-gray-300 rounded-lg p-2">
            </div>

            <label class="flex items-center gap-2 mb-4 cursor-pointer">
                <input type="checkbox" class="is-color-checkbox h-4 w-4 text-blue-600 rounded">
                <span class="text-xs text-gray-500">این ویژگی مربوط به رنگ است</span>
            </label>

            <label class="block mb-2 font-semibold text-gray-700">Values</label>
            <div class="values-wrapper"></div>

            <button type="button" class="add-value-btn text-sm text-blue-600 hover:text-blue-800 font-semibold mt-1">
                + افزودن مقدار جدید
            </button>
        </div>
    </template>

    <template id="valueTemplate">
        <div class="value-row flex items-center gap-2 mb-2">
            <input type="text" class="value-input flex-grow border-gray-300 rounded-lg p-2" placeholder="مقدار">

            <div class="color-picker-wrapper hidden items-center gap-2" style="display:none;">
                <input type="color" class="color-input h-10 w-10 border border-gray-300 rounded cursor-pointer p-0"
                    value="#000000">
                <span class="color-hex-label text-xs text-gray-500 w-16">#000000</span>
            </div>

            <button type="button" class="remove-value-btn text-red-500 hover:text-red-700 px-2">✕</button>
        </div>
    </template>

    <script>
        (function() {
            const wrapper = document.getElementById('attributesWrapper');
            const attributeTemplate = document.getElementById('attributeTemplate');
            const valueTemplate = document.getElementById('valueTemplate');
            const addAttributeBtn = document.getElementById('addAttributeBtn');

            let attributeIndex = 0;

            function addAttribute() {
                const clone = attributeTemplate.content.cloneNode(true);
                const block = clone.querySelector('.attribute-block');
                const currentIndex = attributeIndex++;

                const nameInput = block.querySelector('.attribute-name-input');
                nameInput.name = `attributes[${currentIndex}][name]`;

                const colorCheckbox = block.querySelector('.is-color-checkbox');
                colorCheckbox.name = `attributes[${currentIndex}][is_color]`;
                colorCheckbox.value = '1';

                block.querySelector('.remove-attribute-btn').addEventListener('click', function() {
                    block.remove();
                    toggleAttributeRemoveButtons();
                });

                const valuesWrapper = block.querySelector('.values-wrapper');

                // با تغییر چک‌باکس، برای همه‌ی مقدار های موجود پیکر رنگ رو نشون/مخفی کن
                colorCheckbox.addEventListener('change', function() {
                    valuesWrapper.querySelectorAll('.color-picker-wrapper').forEach(function(el) {
                        el.style.display = colorCheckbox.checked ? 'flex' : 'none';
                    });
                });

                block.querySelector('.add-value-btn').addEventListener('click', function() {
                    addValue(valuesWrapper, currentIndex, colorCheckbox.checked);
                });

                wrapper.appendChild(block);

                addValue(valuesWrapper, currentIndex, false);
                toggleAttributeRemoveButtons();
            }

            function addValue(valuesWrapper, attrIndex, showColor) {
                const clone = valueTemplate.content.cloneNode(true);
                const row = clone.querySelector('.value-row');
                const valueIndex = valuesWrapper.querySelectorAll('.value-row').length;

                const input = row.querySelector('.value-input');
                input.name = `attributes[${attrIndex}][values][${valueIndex}]`;

                const colorWrapper = row.querySelector('.color-picker-wrapper');
                const colorInput = row.querySelector('.color-input');
                const hexLabel = row.querySelector('.color-hex-label');

                colorInput.name = `attributes[${attrIndex}][colors][${valueIndex}]`;
                colorWrapper.style.display = showColor ? 'flex' : 'none';

                // با انتخاب رنگ از پیکر، مقدار hex نمایشی هم آپدیت بشه
                colorInput.addEventListener('input', function() {
                    hexLabel.textContent = colorInput.value;
                });

                row.querySelector('.remove-value-btn').addEventListener('click', function() {
                    row.remove();
                    reindexValues(valuesWrapper, attrIndex);
                    toggleValueRemoveButtons(valuesWrapper);
                });

                valuesWrapper.appendChild(row);
                toggleValueRemoveButtons(valuesWrapper);
            }

            function reindexValues(valuesWrapper, attrIndex) {
                valuesWrapper.querySelectorAll('.value-row').forEach(function(row, idx) {
                    row.querySelector('.value-input').name = `attributes[${attrIndex}][values][${idx}]`;
                    row.querySelector('.color-input').name = `attributes[${attrIndex}][colors][${idx}]`;
                });
            }

            function toggleValueRemoveButtons(valuesWrapper) {
                const rows = valuesWrapper.querySelectorAll('.value-row');
                rows.forEach(function(row) {
                    row.querySelector('.remove-value-btn').style.display = rows.length > 1 ? 'inline-block' :
                        'none';
                });
            }

            function toggleAttributeRemoveButtons() {
                const blocks = wrapper.querySelectorAll('.attribute-block');
                blocks.forEach(function(block) {
                    block.querySelector('.remove-attribute-btn').style.display = blocks.length > 1 ?
                        'inline-block' : 'none';
                });
            }

            addAttributeBtn.addEventListener('click', addAttribute);
            addAttribute();
        })();
    </script>
</x-layout>
