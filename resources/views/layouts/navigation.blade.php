<nav x-data="{ open: false }" class="bg-white border-b border-teal-100 shadow-sm">
    <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between min-h-[64px] items-center">
            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0 py-2">
                    <span class="flex items-center justify-center w-12 h-12 rounded-xl bg-teal-600 text-white">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v8M8 12h8"/>
                        </svg>
                    </span>
                    <span class="hidden sm:inline text-base font-semibold text-slate-800">Учёт мед. оборудования</span>
                </a>

                <div class="hidden space-x-2 sm:ms-6 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        Главная
                    </x-nav-link>
                    <x-nav-link :href="route('equipment.index')" :active="request()->routeIs('equipment.*')">
                        Список оборудования
                    </x-nav-link>
                    @if(Auth::user()->isAdmin())
                        <x-nav-link :href="route('equipment-requests.index')" :active="request()->routeIs('equipment-requests.*')">
                            Заявки
                        </x-nav-link>
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center min-h-[48px] px-4 py-3 text-base font-medium text-slate-700 rounded-xl hover:bg-teal-50 hover:text-teal-700 focus:outline-none transition border border-transparent hover:border-teal-100">
                                    Справочники
                                    <svg class="ms-1 h-5 w-5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('equipment-types.index')">Вид оборудования (категория)</x-dropdown-link>
                                <x-dropdown-link :href="route('departments.index')">Отделы</x-dropdown-link>
                                <x-dropdown-link :href="route('cabinets.index')">Помещение / Кабинет</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            Пользователи
                        </x-nav-link>
                        <x-nav-link :href="route('admin.activity-archive')" :active="request()->routeIs(['admin.activity-archive', 'admin.activity-archive.clear', 'admin.activity-archive.restore'])">
                            Архив и журнал
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center min-h-[48px] px-4 py-3 text-base font-medium text-slate-700 rounded-xl hover:bg-teal-50 focus:outline-none transition">
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="ms-1 h-5 w-5 text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">Профиль</x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Выйти
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = ! open" class="min-h-[48px] min-w-[48px] flex items-center justify-center rounded-xl text-slate-500 hover:bg-teal-50" aria-label="Меню">
                    <svg class="h-7 w-7" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-teal-100">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Главная</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('equipment.index')" :active="request()->routeIs('equipment.*')">Список оборудования</x-responsive-nav-link>
            @if(Auth::user()->isAdmin())
                <x-responsive-nav-link :href="route('equipment-requests.index')" :active="request()->routeIs('equipment-requests.*')">Заявки</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('equipment-types.index')" :active="request()->routeIs('equipment-types.*')">Вид оборудования</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('departments.index')" :active="request()->routeIs('departments.*')">Отделы</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cabinets.index')" :active="request()->routeIs('cabinets.*')">Помещение / Кабинет</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">Пользователи</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.activity-archive')" :active="request()->routeIs(['admin.activity-archive', 'admin.activity-archive.clear', 'admin.activity-archive.restore'])">Архив и журнал</x-responsive-nav-link>
            @endif
        </div>
        <div class="pt-4 pb-3 border-t border-teal-100">
            <div class="px-4 text-base font-medium text-slate-800">{{ Auth::user()->name }}</div>
            <div class="px-4 text-base text-slate-500">{{ Auth::user()->email }}</div>
            <div class="mt-2 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">Профиль</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Выйти</x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
