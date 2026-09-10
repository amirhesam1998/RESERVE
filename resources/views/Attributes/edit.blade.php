<x-layout title="Edit Attribute">
    <main class="flex-grow container mx-auto px-6 py-10">
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow">
            <h2 class="text-3xl font-bold mb-6 text-center">Edit Attribute</h2>

            <form action="{{ route('attributes.update', $attribute->id) }}" method="post" id="attributeEditForm">
                @csrf
                @method('PUT')

                <div class="mb-2">
                    <label class="block mb-1 font-semibold text-gray-700">Attribute name</label>
                    <input type="text" name="name" value="{{ old('name', $attribute->name) }}"
                        class="w-full border-gray-300 rounded-lg p-2">
                </div>
                @error('name')
                    <span class="text-red-600 text-sm font-bold block mb-2">{{ $message }}</span>
                @enderror

                <label class="flex items-center gap-2 mb-4 cursor-pointer">
                    <input type="checkbox" id="isColorCheckbox" name="is_color" value="1"
                        class="h-4 w-4 text-blue-600 rounded"
                        {{ old('is_color', $attribute->is_color) ? 'checked' : '' }}>
                    <span class="text-xs text-gray-500">این ویژگی مربوط به رنگ است</span>
                </label>

                <hr class="my-4 border-gray-200">

                <label class="block mb-2 font-semibold text-gray-700">Values</label>
                <div id="valuesWrapper">
                    @foreach ($attribute->attributeValues as $value)
                        <div class="value-row flex items-center gap-2 mb-2" data-existing="true">
                            <input type="hidden" name="values[{{ $loop->index }}][id]" value="{{ $value->id }}">

                            <input type="text" name="values[{{ $loop->index }}][value]"
                                value="{{ old('values.' . $loop->index . '.value', $value->value) }}"
                                class="value-input flex-grow border-gray-300 rounded-lg p-2" placeholder="مقدار">

                            <div class="color-picker-wrapper flex items-center gap-2"
                                style="{{ $attribute->is_color ? '' : 'display:none;' }}">
                                <input type="color"
                                    class="color-input h-10 w-10 border border-gray-300 rounded cursor-pointer p-0"
                                    name="values[{{ $loop->index }}][color]"
                                    value="{{ old('values.' . $loop->index . '.color', $value->color_code ?? '#000000') }}">
                                <span class="color-hex-label text-xs text-gray-500 w-16">
                                    {{ $value->color_code ?? '#000000' }}
                                </span>
                            </div>

                            <button type="button"
                                class="remove-value-btn text-red-500 hover:text-red-700 px-2">✕</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" id="addValueBtn"
                    class="text-sm text-blue-600 hover:text-blue-800 font-semibold mt-1">
                    + افزودن مقدار جدید
                </button>

                @error('values')
                    <span class="text-red-600 text-sm font-bold block mt-4">{{ $message }}</span>
                @enderror

                <hr class="my-6 border-gray-200">

                <button type="submit"
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                    ذخیره تغییرات
                </button>
            </form>
        </div>
    </main>

    <template id="valueTemplate">
        <div class="value-row flex items-center gap-2 mb-2">
            <input type="text" class="value-input flex-grow border-gray-300 rounded-lg p-2" placeholder="مقدار">

            <div class="color-picker-wrapper flex items-center gap-2" style="display:none;">
                <input type="color" class="color-input h-10 w-10 border border-gray-300 rounded cursor-pointer p-0"
                    value="#000000">
                <span class="color-hex-label text-xs text-gray-500 w-16">#000000</span>
            </div>

            <button type="button" class="remove-value-btn text-red-500 hover:text-red-700 px-2">✕</button>
        </div>
    </template>

    <script>
        (function() {
            const valuesWrapper = document.getElementById('valuesWrapper');
            const valueTemplate = document.getElementById('valueTemplate');
            const addValueBtn = document.getElementById('addValueBtn');
            const isColorCheckbox = document.getElementById('isColorCheckbox');

            // ایندکس بعدی رو بر اساس تعداد ردیف‌های موجود (که از بک‌اند رندر شدن) شروع می‌کنیم
            let valueIndex = document.querySelectorAll('#valuesWrapper .value-row').length;

            function bindRemove(row) {
                row.querySelector('.remove-value-btn').addEventListener('click', function() {
                    row.remove();
                    toggleRemoveButtons();
                });
            }

            function bindColorInput(row) {
                const colorInput = row.querySelector('.color-input');
                const hexLabel = row.querySelector('.color-hex-label');
                colorInput.addEventListener('input', function() {
                    hexLabel.textContent = colorInput.value;
                });
            }

            function addValue() {
                const clone = valueTemplate.content.cloneNode(true);
                const row = clone.querySelector('.value-row');
                const currentIndex = valueIndex++;

                const input = row.querySelector('.value-input');
                input.name = `values[${currentIndex}][value]`;

                const colorInput = row.querySelector('.color-input');
                colorInput.name = `values[${currentIndex}][color]`;

                const colorWrapper = row.querySelector('.color-picker-wrapper');
                colorWrapper.style.display = isColorCheckbox.checked ? 'flex' : 'none';

                bindColorInput(row);
                bindRemove(row);

                valuesWrapper.appendChild(row);
                toggleRemoveButtons();
            }

            function toggleRemoveButtons() {
                const rows = valuesWrapper.querySelectorAll('.value-row');
                rows.forEach(function(row) {
                    row.querySelector('.remove-value-btn').style.display = rows.length > 1 ? 'inline-block' :
                        'none';
                });
            }

            // اتصال دکمه‌های حذف و رنگ برای ردیف‌های موجود که از بک‌اند رندر شدن
            document.querySelectorAll('#valuesWrapper .value-row').forEach(function(row) {
                bindColorInput(row);
                bindRemove(row);
            });

            // با تغییر چک‌باکس، پیکر رنگ همه‌ی ردیف‌ها (قدیمی و جدید) نشون/مخفی بشه
            isColorCheckbox.addEventListener('change', function() {
                valuesWrapper.querySelectorAll('.color-picker-wrapper').forEach(function(el) {
                    el.style.display = isColorCheckbox.checked ? 'flex' : 'none';
                });
            });

            addValueBtn.addEventListener('click', addValue);
            toggleRemoveButtons();
        })();
    </script>
</x-layout>
