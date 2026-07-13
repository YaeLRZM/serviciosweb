<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<div class="contents">
    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-[#D81B60]/20 bg-[#D81B60] text-[#F8F5F2] [--flux-sidebar-bg:#D81B60]">

        <flux:sidebar.header class="flex flex-col items-start gap-2 py-6">
            <h1 style="font-family: 'Cinzel', serif;" class="text-3xl font-medium leading-none tracking-wide text-[#F8F5F2]">
                IXÉ<br><span class="text-xl opacity-90">MODA</span>
            </h1>
            <flux:sidebar.collapse class="lg:hidden text-[#F8F5F2]" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group heading="Menú" class="grid gap-2 text-[#F8F5F2]">
                <x-item-sidebar icon="home" ruta="admin.dashboard" texto="Página principal" />
                <x-item-sidebar icon="publications" ruta="admin.publicacion.index" texto="Publicaciones" />
                <x-item-sidebar icon="package" ruta="admin.categorias.index" texto="Categorías" />
                <x-item-sidebar icon="user" ruta="admin.artesanos.index" texto="Artesanos" />
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <div class="border-t border-[#F8F5F2]/20 pt-4">
            <flux:dropdown position="top" align="start" class="w-full">
                <button class="flex items-center gap-3 w-full text-left px-2 py-1.5 rounded-lg hover:bg-[#F8F5F2]/10 text-[#F8F5F2] transition-colors">
                    <x-icon.user variant="mini" />
                    <span class="truncate font-medium flex-1">{{ auth()->user()->name ?? 'Administrador' }}</span>
                </button>
                <flux:menu class="bg-[#F8F5F2] text-zinc-900 border border-zinc-200 p-1">
                    <flux:menu.item :href="route('admin.profile')" icon="cog" wire:navigate>
                        {{ __('Ajustes de Perfil') }}
                    </flux:menu.item>
                    <flux:menu.separator />

                    <button
                        type="button"
                        wire:click="logout"
                        class="flex w-full items-center gap-2 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 hover:text-red-950 rounded-md transition-all duration-200 text-left cursor-pointer">
                        <x-icon.arrow-right-start-on-rectangle variant="mini" />
                        <span>{{ __('Cerrar Sesión') }}</span>
                    </button>
                </flux:menu>
            </flux:dropdown>
        </div>
    </flux:sidebar>

    <flux:header class="lg:hidden bg-[#D81B60] text-[#F8F5F2]">
        <button x-on:click="$store.flux.sidebar.toggle()" class="text-[#F8F5F2] p-2 hover:bg-[#F8F5F2]/10 rounded-lg">
            <x-icon.bars-2 variant="mini" />
        </button>
        <flux:spacer />

        <flux:dropdown position="bottom" align="end">
            <button class="text-[#F8F5F2] p-2 hover:bg-[#F8F5F2]/10 rounded-lg">
                <x-icon.user variant="mini" />
            </button>
            <flux:menu class="bg-[#F8F5F2] text-zinc-900">
                <div class="px-3 py-2 border-b border-zinc-200/50">
                    <p class="font-medium text-sm truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-zinc-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <flux:menu.item :href="route('admin.profile')" icon="cog" wire:navigate>
                    {{ __('Mi Perfil') }}
                </flux:menu.item>
                <flux:menu.separator />

                <button
                    type="button"
                    wire:click="logout"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 hover:text-red-950 rounded-md transition-all duration-200 text-left cursor-pointer">
                    <x-icon.arrow-right-start-on-rectangle variant="mini" />
                    <span>{{ __('Cerrar sesión') }}</span>
                </button>
            </flux:menu>
        </flux:dropdown>
    </flux:header>
</div>