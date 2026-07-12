<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#2B2B2B] leading-tight">
            {{ __('Página principal') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <livewire:admin.dashboard.overview-card />
                    <livewire:admin.dashboard.income-chart />
                </div>

                <div class="space-y-6">
                    <livewire:admin.dashboard.popular-products />
                    <livewire:admin.dashboard.recent-reports />
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>