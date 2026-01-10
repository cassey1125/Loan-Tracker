<?php

namespace App\Livewire\Borrowers;

use App\Repositories\BorrowerRepository;
use Livewire\Component;
use Livewire\WithPagination;

class BorrowerList extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render(BorrowerRepository $repository)
    {
        return view('livewire.borrowers.borrower-list', [
            'borrowers' => $repository->getBorrowers(
                $this->search,
                $this->status,
                $this->sortBy,
                $this->sortDirection
            ),
        ]);
    }
}
