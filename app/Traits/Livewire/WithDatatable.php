<?php

namespace App\Traits\Livewire;

use Livewire\Attributes\On;
use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;

trait WithDatatable
{
    use WithoutUrlPagination, WithPagination;

    protected $paginationTheme = 'tailwind';

    public $pageName = '';

    public $lengthOptions = [10, 25, 50, 100];

    public $length = 50;

    public $search;

    public $sortBy = '';

    public $sortDirection = 'asc';

    public $keyword_filter = true;

    public $show_filter = true;

    public $showLoadingIndicator = true;

    public $loadingIndicatorTarget = '';

    abstract public function getColumns(): array;

    abstract public function getQuery();

    abstract public function getView(): string;

    public function onMount() {}

    public function mount()
    {
        $this->onMount();

        $columns = $this->getColumns();
        if ($this->sortBy == '' && count($columns) > 0) {
            foreach ($columns as $col) {
                if (! isset($col['sortable']) || $col['sortable']) {
                    $this->sortBy = $col['key'];
                    break;
                }
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('datatable-add-filter')]
    public function datatableAddFilter($filter)
    {
        foreach ($filter as $key => $value) {
            $this->$key = $value;
        }
    }

    #[On('datatable-refresh')]
    public function datatableRefresh()
    {
        $this->resetPage();
    }

    public function datatablePaginate($query)
    {
        if ($this->pageName) {
            return $query->paginate($this->length, pageName: $this->pageName);
        } else {
            return $query->paginate($this->length);
        }
    }

    public function datatableSort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc'
                ? 'desc'
                : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortBy = $field;
    }

    public function datatableGetProcessedQuery()
    {
        $columns = $this->getColumns();
        $query = $this->getQuery();
        $search = $this->search;
        $sortBy = $this->sortBy;
        $sortDirection = $this->sortDirection;

        $likeOperator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        $query->when($search, function ($query) use ($search, $columns, $likeOperator) {
            $query->where(function ($query) use ($columns, $search, $likeOperator) {
                foreach ($columns as $col) {
                    if (
                        isset($col['key'])
                        && (! isset($col['searchable']) || (isset($col['searchable']) && $col['searchable']))
                    ) {
                        $query->orWhere($col['key'], $likeOperator, "%$search%");
                    }
                }
            });
        });

        $query->when($sortBy, function ($query) use ($sortBy, $sortDirection) {
            $query->orderBy($sortBy, $sortDirection);
        });

        return $query;
    }

    public function datatableGetData()
    {
        return $this->datatablePaginate($this->datatableGetProcessedQuery());
    }

    public function render()
    {
        return view($this->getView(), [
            'data' => $this->datatableGetData(),
            'columns' => $this->getColumns(),
        ]);
    }
}
