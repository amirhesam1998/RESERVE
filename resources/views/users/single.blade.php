<x-layout title="Manage User: {{ $user->first_name }}">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">


        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm flex items-center">
                <svg class="h-5 w-5 text-green-400 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">


            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center space-x-4 mb-6">
                        <div
                            class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xl">
                            {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $user->first_name }} {{ $user->last_name }}
                            </h3>
                            {{--                             <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Client
                            </span> --}}
                        </div>
                    </div>

                    <div class="space-y-3 text-sm text-gray-600 mb-6">
                        <p class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ $user->email }}
                        </p>
                        <p class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                            {{ $user->phone_number ?? 'No phone provided' }}
                        </p>
                    </div>


                    <div class="flex flex-col space-y-2 pt-4 border-t border-gray-100">
                        @can('users', [$user, 'edit-clients'])
                            <a href="{{ route('users.edit', $user->id) }}"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Edit Information
                            </a>
                        @endcan

                        @can('users', [$user, 'editpass-clients'])
                            <a href="{{ route('user.editpass.form', $user->id) }}"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Change Password
                            </a>
                        @endcan

                        <a href="posts/create"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Read More Posts
                        </a>
                    </div>
                </div>


                @can('users', [$user, 'delete-clients'])
                    <div class="bg-red-50 p-6 rounded-xl border border-red-100">
                        <h4 class="text-sm font-bold text-red-800 mb-2">Danger Zone</h4>
                        <p class="text-xs text-red-600 mb-4">Once you delete this account, there is no going back. Please be
                            certain.</p>
                        <form action="{{ route('users.destroy', $user->id) }}" method="post"
                            onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('delete')
                            <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Delete Account
                            </button>
                        </form>
                    </div>
                @endcan
            </div>


            <div class="lg:col-span-2 space-y-6">


                @if (auth()->user()->can('salons', ['view-salons']) ||
                        auth()->user()->can('salons', ['create-salons']))
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h4 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Salons Management</h4>
                        <div class="flex flex-wrap gap-3">
                            @can('salons', ['view-salons'])
                                <a href="{{ route('salons.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                                    View All Salons
                                </a>
                            @endcan
                            @can('salons', ['create-salons'])
                                <a href="{{ route('salons.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                                    + Create New Salon
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif


                @if (auth()->user()->can('sessions', ['view-sessions']) ||
                        auth()->user()->can('sessions', ['create-sessions']))
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h4 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Sessions Management</h4>
                        <div class="flex flex-wrap gap-3">
                            @can('sessions', ['view-sessions'])
                                <a href="{{ route('sessions.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                                    View All Sessions
                                </a>
                            @endcan
                            @can('sessions', ['create-sessions'])
                                <a href="{{ route('session.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                                    + Create New Session
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif

                <!-- Attributes Module -->
                @if (auth()->user()->can('attributes', ['view-attributes']) ||
                        auth()->user()->can('attributes', ['create-attributes']))
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <h4 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Attributes Management</h4>
                        <div class="flex flex-wrap gap-3">
                            @can('attributes', ['view-attributes'])
                                <a href="{{ route('attributes') }}"
                                    class="inline-flex items-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                                    View All Attributes
                                </a>
                            @endcan
                            @can('attributes', ['create-attributes'])
                                <a href="{{ route('attributes.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg text-sm font-medium text-blue-700 hover:bg-blue-100 transition-colors">
                                    + Create New Attribute
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-layout>
