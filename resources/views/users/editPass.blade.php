 <x-layout titile="edit Password">
     <main class="flex-grow container mx-auto px-6 py-10">
         <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow">
             <h2 class="text-3xl font-bold mb-6 text-center">edit Password</h2>

             @php
                 $titleField = 'mb-4 bg-red-600';
             @endphp

             <form action="{{ route('user.editPass', $user->id) }}" method="post">
                 @csrf
                 @method('PATCH')
                 <div class="mb-5"></div>
                 <x-input label="currunt password" name="curruntPass" type="password" class="mb-4">
                 </x-input>
                 @error('curruntPass')
                     <span class="bg-red-300 font-bold">{{ $message }} </span>
                 @enderror

                 <x-input label="new password" type="password" name="newPass" class="mb-4">
                 </x-input>
                 @error('newPass')
                     <span class="bg-blue-600 font-bold">{{ $message }} </span>
                 @enderror

                 <x-input label="new password again" type="password" name="newPass_confirmation"
                     class="mb-4"></x-input>
                 @error('newPass_confirmation')
                     <span class="bg-blue-600 font-bold">{{ $message }} </span>
                 @enderror

                 <button type="submit"
                     class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
                     Edit user
                 </button>

             </form>
         </div>
     </main>

 </x-layout>
