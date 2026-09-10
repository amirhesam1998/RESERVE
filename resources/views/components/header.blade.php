<header class="bg-blue-600 text-white shadow">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Salon Mangement</h1>
        @auth
            <div class="flex items-center gap-4">
                <form action="{{ route('user.logout') }}" method="post">
                    @csrf
                    <button class="text-blue-600 text-white bg-red-500 px-4 py-2 rounded-lg cursor-pointer">
                        Logout
                    </button>
                </form>

                @can('users-onlyPermission', [auth()->user(), 'create-clients'])
                    <a href="{{ route('user.newuserform') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        create new user
                    </a>
                @endcan

                @can('users-onlyPermission', ['view-clients'])
                    <a href="{{ route('users.index') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Users
                    </a>
                @endcan

                @can('users-onlyPermission', ['view-admins'])
                    <a href="{{ route('user.admins') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Admins
                    </a>
                @endcan

                @can('showone_user', [auth()->user(), 'view-clients'])
                    <a href="{{ route('users.show', auth()->user()) }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Dashboard
                    </a>
                @endcan

                |

                @can('roles', ['view-roles'])
                    <a href="{{ route('roles.index') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Roles
                    </a>
                @endcan


                @can('roles', ['create-roles'])
                    <a href="{{ route('roles.create') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Create Roles
                    </a>
                @endcan

                |

                @can('categories', ['view-categories'])
                    <a href="{{ route('category.index') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Categories
                    </a>
                @endcan

                @can('categories', ['create-category'])
                    <a href="{{ route('category.create') }}"
                        class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                        Create Category
                    </a>
                @endcan


                <a href="{{ route('carts.show', auth()->user()) }}"
                    class="text-blue-600 text-white bg-blue-500 px-4 py-2 rounded-lg cursor-pointer">
                    Cart items
                </a>
            </div>
        @endauth
    </div>
</header>
