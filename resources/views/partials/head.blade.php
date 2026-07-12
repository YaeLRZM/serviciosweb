<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:header container class="border-b border-[#D81B60]/20 bg-[#D81B60] text-[#F8F5F2] [--flux-header-bg:#D81B60]">

        <flux:sidebar.toggle class="lg:hidden mr-2 text-[#F8F5F2]" inset="left" />

        <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center me-6">
            <h1 style="font-family: 'Cinzel', serif;" class="text-xl font-medium tracking-wide text-[#F8F5F2] leading-none">
                IXÉ MODA
            </h1>
        </a>

        <flux:navbar class="-mb-px max-lg:hidden">
            @if(request()->routeIs('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 px-3 h-10 rounded-lg bg-[#F8F5F2] text-[#D81B60] font-medium transition-colors">
                <x-icon.home variant="mini" />
                <span>{{ __('Dashboard') }}</span>
            </a>
            @else
            <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 px-3 h-10 rounded-lg text-[#F8F5F2] hover:bg-[#F8F5F2]/10 font-medium transition-colors">
                <x-icon.home variant="mini" />
                <span>{{ __('Dashboard') }}</span>
            </a>
            @endif
        </flux:navbar>

        <flux:spacer />

        <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
            <flux:tooltip :content="__('Search')" position="bottom">
                <button class="text-[#F8F5F2] p-2 hover:bg-[#F8F5F2]/10 rounded-lg transition-colors">
                    <x-icon.envelope variant="mini" /> </button>
            </flux:tooltip>
        </flux:navbar>

        <flux:dropdown position="bottom" align="end">
            <button class="flex items-center gap-2 text-[#F8F5F2] hover:bg-[#F8F5F2]/10 px-3 py-1.5 rounded-lg transition-colors">
                <x-icon.user variant="mini" />
                <span class="max-lg:hidden">{{ auth()->user()->name }}</span>
            </button>
            <flux:menu class="bg-[#F8F5F2] text-zinc-900">
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                    {{ __('Mi Perfil') }}
                </flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full text-red-600">
                        {{ __('Cerrar sesión') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-[#D81B60]/20 bg-[#D81B60] text-[#F8F5F2] [--flux-sidebar-bg:#D81B60]">
        <flux:sidebar.header class="flex flex-col items-start gap-2 py-6">
            <h1 style="font-family: 'Cinzel', serif;" class="text-3xl font-medium leading-none tracking-wide text-[#F8F5F2]">
                IXÉ<br><span class="text-xl opacity-90">MODA</span>
            </h1>
            <flux:sidebar.collapse class="text-[#F8F5F2]" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group :heading="__('Platform')" class="grid gap-1 text-[#F8F5F2]/80">

                @if(request()->routeIs('admin.dashboard'))
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg bg-[#F8F5F2] text-[#D81B60] font-medium transition-colors">
                    <x-icon.home variant="mini" />
                    <span>{{ __('Dashboard') }}</span>
                </a>
                @else
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#F8F5F2] hover:bg-[#F8F5F2]/10 font-medium transition-colors">
                    <x-icon.home variant="mini" />
                    <span>{{ __('Dashboard') }}</span>
                </a>
                @endif

            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />
    </flux:sidebar>

    <flux:main>
        {{ $slot }}
    </flux:main>

    @fluxScripts
</body>

</html>