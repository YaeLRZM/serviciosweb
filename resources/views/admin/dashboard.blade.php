<x-admin-layout>
    <x-slot name="header">
        Página principal
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda -->
                <div class="lg:col-span-2 space-y-6">
                    <livewire:admin.dashboard.overview-card />
                    <livewire:admin.dashboard.income-chart />
                </div>

                <!-- Columna Derecha -->
                <div class="space-y-6">
                    <!-- Invertimos el orden para coincidir con la imagen -->
                    <livewire:admin.dashboard.recent-reports />
                    <livewire:admin.dashboard.popular-products />
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>