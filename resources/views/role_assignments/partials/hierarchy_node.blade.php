<div class="hierarchy-node" data-level="{{ $level }}">
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="d-flex align-items-center">
                @if($node['employee']->photo)
                    <img src="{{ asset('storage/' . $node['employee']->photo) }}" class="avatar me-3" alt="{{ $node['employee']->first_name }}">
                @else
                    <div class="avatar-placeholder me-3">
                        {{ substr($node['employee']->first_name, 0, 1) }}{{ substr($node['employee']->last_name, 0, 1) }}
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        {{ $node['employee']->first_name }} {{ $node['employee']->last_name }}
                        <span class="badge bg-light text-dark ms-2">{{ $node['employee']->employee_code }}</span>
                    </div>
                    <div class="text-muted small">
                        {{ $node['employee']->employmentDetail->designation ?? 'N/A' }} |
                        {{ $node['employee']->employmentDetail->department ?? 'N/A' }}
                    </div>
                </div>
                <div class="text-end">
                    @if($node['employee']->user)
                        @switch($node['employee']->user->role)
                            @case('admin')
                                <span class="badge bg-danger role-badge">Admin</span>
                                @break
                            @case('cto')
                                <span class="badge bg-dark role-badge">CTO</span>
                                @break
                            @case('hr')
                                <span class="badge bg-info role-badge">HR</span>
                                @break
                            @case('manager')
                                <span class="badge bg-warning role-badge">Manager</span>
                                @break
                            @case('employee')
                                <span class="badge bg-secondary role-badge">Employee</span>
                                @break
                            @default
                                <span class="badge bg-light text-dark role-badge">{{ $node['employee']->user->role }}</span>
                        @endswitch
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if(count($node['subordinates']) > 0)
        <div class="subordinates">
            @foreach($node['subordinates'] as $subordinate)
                @include('role_assignments.partials.hierarchy_node', ['node' => $subordinate, 'level' => $level + 1])
            @endforeach
        </div>
    @endif
</div>
