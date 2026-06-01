<?php

namespace App\Livewire\Soporte;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;

class TicketsTable extends Component
{
    use WithPagination;

    public $search = '';
    public $filtroEstado = '1'; // Default: Por Asignar
    public $modoGestor = false;

    // Reset pagination when searching
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function mount()
    {
        // Si el usuario es gestor, puede ver tickets de todos. Si no, solo los suyos asignados.
        // Pero en la ruta /soporte/tickets, típicamente es el gestor.
        $this->modoGestor = Auth::user()->hasRole('gestor') || Auth::user()->hasRole('admin');
    }

    public function render()
    {
        $query = Ticket::with(['usuario', 'tecnico', 'categoria']);

        if (!$this->modoGestor) {
            // El técnico solo ve sus tickets asignados o en los que participa
            $query->where('tecnico_id', Auth::id());
        }

        if ($this->filtroEstado !== 'todos') {
            $query->where('estatus', $this->filtroEstado);
        }

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('asunto', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('usuario', function($qu) {
                      $qu->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('tecnico', function($qt) {
                      $qt->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.soporte.tickets-table', compact('tickets'));
    }
}
