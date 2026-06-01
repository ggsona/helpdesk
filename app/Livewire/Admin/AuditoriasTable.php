<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AuditoriasTable extends Component
{
    use WithPagination;

    public $search = '';
    public $action = '';
    public $type = '';

    protected $paginationTheme = 'bootstrap';

    protected $queryString = [
        'search' => ['except' => ''],
        'action' => ['except' => ''],
        'type' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'action', 'type']);
        $this->resetPage();
    }

    public function updatingAction()
    {
        $this->resetPage();
    }

    public function updatingType()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('subject_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($this->action)) {
            $query->where('event', $this->action);
        }

        if (!empty($this->type)) {
            if ($this->type === 'User') {
                $query->where(function ($q) {
                    $q->where('subject_type', 'like', "%{$this->type}%")
                      ->orWhere('log_name', 'Autenticación');
                });
            } else {
                $query->where('subject_type', 'like', "%{$this->type}%");
            }
        }

        $logs = $query->paginate(15);

        return view('livewire.admin.auditorias-table', compact('logs'));
    }
}
