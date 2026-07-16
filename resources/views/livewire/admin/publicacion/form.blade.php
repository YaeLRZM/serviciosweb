<?php

use App\Livewire\Concerns\InteractsWithApi;
use App\Livewire\Concerns\RequiresApiAuth;
use App\Services\Api\ArticuloApiService;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use RequiresApiAuth, InteractsWithApi;

    public bool $isOpen = false;
    public ?int $articuloId = null;

    // Campos que pide UpdateArticuloRequest
    public string $nombre = '';
    public ?string $descripcion = '';
    public $precio = null;
    public $stock = null;

    public ?string $error = null;

    protected function rules(): array
    {
        return [
            'nombre'      => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'precio'      => ['required', 'numeric', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
        ];
    }

    /** Abre el popup de edición y precarga el artículo desde el API. */
    #[On('editarArticulo')]
    public function abrir($id): void
    {
        $this->resetValidation();
        $this->reset(['nombre', 'descripcion', 'precio', 'stock', 'error']);
        $this->articuloId = (int) $id;

        try {
            $respuesta = app(ArticuloApiService::class)->find($this->articuloId);

            if (! $respuesta->successful()) {
                $this->error = 'No se pudo cargar el artículo.';
                $this->isOpen = true;
                return;
            }

            $data = $respuesta->json('data', []);
            $this->nombre      = (string) ($data['nombre'] ?? '');
            $this->descripcion = $data['descripcion'] ?? '';
            $this->precio      = $data['precio'] ?? null;
            $this->stock       = $data['stock'] ?? null;
        } catch (\Throwable $e) {
            $this->error = 'No se pudo conectar con el API.';
        }

        $this->isOpen = true;
    }

    /** Guarda los cambios llamando a PUT /api/articulos/{id}. */
    public function guardar(): void
    {
        $data = $this->validate();

        $respuesta = app(ArticuloApiService::class)->update($this->articuloId, $data);

        if (! $this->handleApiResponse($respuesta, 'Artículo actualizado correctamente.')) {
            return;
        }

        $this->isOpen = false;
        $this->dispatch('articulo-actualizado');
    }

    /** Elimina el artículo llamando a DELETE /api/articulos/{id}. */
    #[On('eliminarArticulo')]
    public function eliminar($id): void
    {
        $respuesta = app(ArticuloApiService::class)->remove((int) $id);

        if (! $this->handleApiResponse($respuesta, 'Artículo eliminado correctamente.')) {
            return;
        }

        $this->dispatch('articulo-actualizado');
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

            <form wire:submit="guardar" class="p-6 space-y-4">
                {{-- Nombre --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nombre</label>
                    <input type="text" wire:model="nombre"
                        class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                    @error('nombre') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Descripción</label>
                    <textarea wire:model="descripcion" rows="3"
                        class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]"></textarea>
                    @error('descripcion') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    {{-- Precio --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Precio</label>
                        <input type="number" step="0.01" min="0" wire:model="precio"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
                        @error('precio') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Stock --}}
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1.5">Stock</label>
                        <input type="number" min="0" wire:model="stock"
                            class="w-full text-sm rounded-xl border-neutral-200 bg-neutral-50 focus:ring-2 focus:ring-[#D81B60]/20 focus:border-[#D81B60]" />
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
