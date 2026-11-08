<!-- Sidebar -->
<aside class="sidebar" id="app-sidebar" aria-label="Main navigation">
    <div class="sidebar-brand">
        <img src="{{ asset('images/innobles_logo.png') }}" alt="Innobles" class="img-fluid">
        <button type="button" class="sidebar-close" aria-label="Close menu"><i class="bi bi-x-lg"></i></button>
    </div>
    
    <nav class="sidebar-menu">
        <ul>
            <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="{{ Request::is('employees*') ? 'active' : '' }}">
                <a href="{{ route('employees.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Employees</span>
                </a>
            </li>
            <li class="{{ Request::is('attendances*') ? 'active' : '' }}">
                <a href="{{ route('attendances.index') }}">
                    <i class="bi bi-calendar-check"></i>
                    <span>Attendance</span>
                </a>
            </li>
            <li class="{{ Request::is('leave*') ? 'active' : '' }}">
                <a href="{{ route('leave.index') }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Leave Management</span>
                </a>
            </li>
            <li class="{{ Request::is('reimbursements*') ? 'active' : '' }}">
                <a href="{{ route('reimbursements.index') }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Reimbursements</span>
                </a>
            </li>
            @if(auth()->user()->role === 'admin')
                <li class="{{ Request::is('reimbursements/all') ? 'active' : '' }}">
                    <a href="{{ route('reimbursements.all') }}">
                        <i class="bi bi-list-check"></i>
                        <span>All Reimbursements</span>
                    </a>
                </li>
            @endif
            @if(auth()->user()->role === 'admin')
                <li class="{{ (Request::is('payrolls*') && !Request::is('payrolls/generate', 'payrolls/bulk-approval')) ? 'active' : '' }}">
                    <a href="{{ route('payrolls.index') }}">
                        <i class="bi bi-cash-coin"></i>
                        <span>Payroll</span>
                    </a>
                </li>
                <li class="{{ Request::is('payrolls/generate') ? 'active' : '' }}">
                    <a href="{{ route('payrolls.generate') }}">
                        <i class="bi bi-plus-circle"></i>
                        <span>Generate Salary</span>
                    </a>
                </li>
                <li class="{{ Request::is('payrolls/bulk-approval') ? 'active' : '' }}">
                    <a href="{{ route('payrolls.bulk.approval') }}">
                        <i class="bi bi-check-circle"></i>
                        <span>Bulk Approval</span>
                    </a>
                </li>
            @endif
            <li class="{{ Request::is('reports/payment-history') ? 'active' : '' }}">
                <a href="{{ route('reports.payment.history') }}">
                    <i class="bi bi-receipt"></i>
                    <span>Payment History</span>
                </a>
            </li>
            @if(auth()->user()->role === 'admin')
                <li class="{{ (Request::is('reports*') && !Request::is('reports/payment-history')) ? 'active' : '' }}">
                    <a href="{{ route('reports.index') }}">
                        <i class="bi bi-graph-up"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="{{ Request::is('admin*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users') }}">
                        <i class="bi bi-shield-lock"></i>
                        <span>Users & Roles</span>
                    </a>
                </li>
                <li class="{{ Request::is('role-assignments*') ? 'active' : '' }}">
                    <a href="{{ route('role-assignments.index') }}">
                        <i class="bi bi-person-gear"></i>
                        <span>Role Assignments</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-logout w-100">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </button>
        </form>
    </div>
</aside>

