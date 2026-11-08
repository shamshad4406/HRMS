@extends('layouts.app')

@section('title', 'Organization Hierarchy - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Organization Hierarchy</h1>
            <p class="text-muted mb-0">View the complete organization structure</p>
        </div>
        <a href="{{ route('role-assignments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Role Assignments
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="hierarchy-container">
                @if(count($hierarchy) > 0)
                    @foreach($hierarchy as $node)
                        @include('role_assignments.partials.hierarchy_node', ['node' => $node, 'level' => 0])
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-diagram-3 fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No hierarchy data available. Assign managers to employees first.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .hierarchy-container {
            padding: 20px;
        }
        .hierarchy-node {
            margin-bottom: 20px;
        }
        .hierarchy-node .card {
            border-left: 4px solid #0d6efd;
        }
        .hierarchy-node[data-level="0"] .card {
            border-left-color: #dc3545;
        }
        .hierarchy-node[data-level="1"] .card {
            border-left-color: #fd7e14;
        }
        .hierarchy-node[data-level="2"] .card {
            border-left-color: #0d6efd;
        }
        .hierarchy-node[data-level="3"] .card {
            border-left-color: #20c997;
        }
        .subordinates {
            margin-left: 30px;
            margin-top: 15px;
            border-left: 2px dashed #dee2e6;
            padding-left: 20px;
        }
        .role-badge {
            font-size: 0.85em;
        }
    </style>
@endsection
