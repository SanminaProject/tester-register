<?php

namespace App\Livewire\Pages\Issues;

use App\Models\TesterEventLog;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class IssueHistory extends Component
{
    use WithPagination;

    public string $search = '';

    /** @var array<string, string> */
    public array $filters = [
        'id' => 'Log ID',
        'date' => 'Date',
        'tester_id' => 'Tester ID',
        'eventType.name' => 'Type',
        'description' => 'Description',
        'createdBy.first_name' => 'User',
        'createdBy.last_name' => 'User',
        'issueStatusRelation.name' => 'Status',
    ];

    /** @var list<string> */
    public array $activeFilters = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleFilter(string $filter): void
    {
        if (in_array($filter, $this->activeFilters, true)) {
            $this->activeFilters = array_values(array_diff($this->activeFilters, [$filter]));
        } else {
            $this->activeFilters[] = $filter;
        }

        $this->resetPage();
    }

    public function beginAddIssue(): void
    {
        $this->dispatch('switchTab', tab: 'add');
    }

    public function getGroupsProperty()
    {
        $query = TesterEventLog::query()
            ->with([
                'tester',
                'createdBy',
                'resolvedBy',
                'issueStatusRelation',
                'eventType',
                'solutionEntries' => function (Builder $builder): void {
                    $builder->with(['createdBy', 'resolvedBy', 'issueStatusRelation', 'eventType'])
                        ->orderBy('date');
                },
            ])
            ->problems()
            ->orderByDesc('date');

        $searchColumns = [
            'id',
            'date',
            'tester_id',
            'eventType.name',
            'description',
            'createdBy.first_name',
            'createdBy.last_name',
            'issueStatusRelation.name',
            'solutionEntries.description',
            'solutionEntries.resolution_description',
        ];

        if ($this->activeFilters !== []) {
            $searchColumns = array_values(array_intersect($searchColumns, $this->activeFilters));
        }

        $keyword = trim($this->search);
        if ($keyword !== '') {
            $query->where(function (Builder $builder) use ($keyword, $searchColumns) {
                foreach ($searchColumns as $column) {
                    if (str_contains($column, '.')) {
                        [$relation, $relColumn] = explode('.', $column, 2);

                        $builder->orWhereHas($relation, function (Builder $relationQuery) use ($relColumn, $keyword) {
                            $relationQuery->where($relColumn, 'like', '%' . $keyword . '%');
                        });
                    } else {
                        $builder->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }
            });
        }

        $paginator = $query->paginate(10);
        $paginator->getCollection()->transform(function (TesterEventLog $issue): TesterEventLog {
            return $issue;
        });

        return $paginator;
    }

    public function getUserLabelByIdProperty(): array
    {
        return [];
    }

    public function render()
    {
        return view('livewire.pages.issues.issue-history');
    }
}
