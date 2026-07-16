<?php

use App\Services\Articulos\ArticulosDataService;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public bool $isOpen = false;
    public ?int $articuloId = null;

    // Campos reales en pgsql articulos (+ stock en inventarios)
    public string $nombre = '';
    public string $talla = '';
    public string $color = '';
    public string $bordado = '';
    public string $tela = '';
    public string $region = '';
    public $stock = null;

    public ?string $error = null;

    protected function rules(): array
    {
        return [
            'nombre'  => ['required', 'string', 'max:255'],
            'talla'   => ['nullable', 'string'],
            'color'   => ['nullable', 'string'],
            'bordado' => ['nullable', 'string'],
            'tela'    => ['nullable', 'string'],
            'region'  => ['nullable', 'string'],
            'stock'   => ['required', 'integer', 'min:0'],
        ];
    }

    /** Abre el popup de edición (Eloquent local). */
    #[On('editarArticulo')]
    public function abrir($id): void
    {
        $this->resetValidation();
        $this->reset(['nombre', 'talla', 'color', 'bordado', 'tela', 'region', 'stock', 'error']);
        $this->articuloId = (int) $id;

        try {
            $data = app(ArticulosDataService::class)->find($this->articuloId);

            if (! $data) {
                $this->error = 'No se pudo cargar el artículo.';
                $this->isOpen = true;

                return;
            }

            $this->nombre  = (string) ($data['nombre'] ?? '');
            $this->talla   = (string) ($data['talla'] ?? '');
            $this->color   = (string) ($data['color'] ?? '');
            $this->bordado = (string) ($data['bordado'] ?? '');
            $this->tela    = (string) ($data['tela'] ?? '');
            $this->region  = (string) ($data['region'] ?? '');
            $this->stock   = $data['stock'] ?? 0;
        } catch (\Throwable $e) {
            $this->error = 'No se pudo cargar el artículo.';
        }

        $this->isOpen = true;
    }

    public function guardar(): void
    {
        $data = $this->validate();

        try {
            app(ArticulosDataService::class)->actualizar((int) $this->articuloId, $data);
            session()->flash('success', 'Artículo actualizado correctamente.');
        } catch (\Throwable $e) {
            $this->error = 'No se pudo guardar el artículo.';

            return;
        }

        $this->isOpen = false;
        $this->dispatch('articulo-actualizado');
    }

    #[On('eliminarArticulo')]
    public function eliminar($id): void
    {
        try {
            app(ArticulosDataService::class)->eliminar((int) $id);
            session()->flash('success', 'Artículo eliminado correctamente.');
            $this->dispatch('articulo-actualizado');
        } catch (\Throwable $e) {
            session()->flash('error', 'No se pudo eliminar el artículo.');
        }
    }

    public function cerrar(): void
    {
        $this->isOpen = false;
    }
}; ?>

<div>
    @if ($isOpen)
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-[2rem] shadow-xl border border-gray-100 max-w-md w-full overflow-hidden">

            <div class="bg-[#D81B60] px-6 py-5 flex items-center justify-between">
                <h3 class="text-white font-extrabold text-lg">Editar artículo</h3>
                <button wire:click="cerrar" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            @if ($error)
            <div class="m-6 bg-red-50 text-red-700 text-sm rounded-xl p-4 font-medium">{{ $error }}</div>
            @endif

            <form wire:submit="guardar" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nombre</label>
                    <input type="text" wire:model="nombre"
                        class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    @error('nombre') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Talla</label>
                        <input type="text" wire:model="talla"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Color</label>
                        <input type="text" wire:model="color"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Bordado</label>
                        <input type="text" wire:model="bordado"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Tela</label>
                        <input type="text" wire:model="tela"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Región</label>
                        <input type="text" wire:model="region"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Stock</label>
                        <input type="number" min="0" wire:model="stock"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                        <p class="text-[10px] text-neutral-400 mt-1">inventarios.stock_actual</p>
                        @error('stock') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" wire:click="cerrar"
                        class="flex-1 text-sm font-bold text-gray-500 hover:bg-gray-50 px-4 py-2.5 rounded-xl transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 text-sm font-bold text-white bg-[#D81B60] hover:bg-[#b0124a] px-4 py-2.5 rounded-xl transition"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="guardar">Guardar cambios</span>
                        <span wire:loading wire:target="guardar">Guardando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
