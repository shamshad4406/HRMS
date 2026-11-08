<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" type="button" aria-label="Open menu" aria-controls="app-sidebar" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="header-right">
        <div class="dropdown">
            <button class="btn btn-icon position-relative" type="button" data-bs-toggle="dropdown" aria-label="Notifications" id="notificationDropdown">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="font-size: 10px; padding: 3px 6px; display: none;">
                    0
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" id="notificationList" style="max-height: 400px; overflow-y: auto;">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><span class="dropdown-item text-muted" id="noNotifications">No notifications</span></li>
                <li><hr class="dropdown-divider" id="notificationDivider" style="display: none;"></li>
                <li><a class="dropdown-item text-primary" href="#" id="markAllRead" style="display: none;">Mark all as read</a></li>
            </ul>
        </div>

        <div class="user-profile">
            @if(auth()->user()->personalInformation && auth()->user()->personalInformation->photo)
                <img src="{{ asset('storage/' . auth()->user()->personalInformation->photo) }}" class="user-avatar" alt="{{ auth()->user()->name }}">
            @else
                <div class="user-avatar-placeholder">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
            @endif
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <div class="dropdown">
                <button class="btn btn-icon" type="button" data-bs-toggle="dropdown" aria-label="Account menu">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="{{ route('change.password') }}"><i class="bi bi-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications
    function loadNotifications() {
        fetch('{{ route("notifications.index") }}')
            .then(response => response.json())
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                const noNotifications = document.getElementById('noNotifications');
                const badge = document.querySelector('.notification-badge');

                // Update badge
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }

                // Clear existing notifications (except header)
                const items = notificationList.querySelectorAll('li:not(:first-child)');
                items.forEach(item => item.remove());

                if (data.notifications.length === 0) {
                    noNotifications.style.display = 'block';
                    document.getElementById('notificationDivider').style.display = 'none';
                    document.getElementById('markAllRead').style.display = 'none';
                } else {
                    noNotifications.style.display = 'none';

                    data.notifications.forEach(notification => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.className = 'dropdown-item' + (notification.is_read ? ' text-muted' : '');
                        a.href = notification.link || '#';
                        a.dataset.notificationId = notification.id;

                        let icon = 'bi-bell';
                        if (notification.type === 'new_employee') icon = 'bi-person-plus';
                        else if (notification.type === 'leave_request') icon = 'bi-calendar-check';
                        else if (notification.type === 'payroll_generated') icon = 'bi-cash-coin';

                        a.innerHTML = `<i class="bi ${icon} me-2"></i>${notification.title}`;
                        li.appendChild(a);
                        notificationList.appendChild(li);

                        // Mark as read on click
                        a.addEventListener('click', function(e) {
                            if (!notification.is_read) {
                                e.preventDefault();
                                markAsRead(notification.id, notification.link);
                            } else {
                                // Close dropdown if already read
                                const dropdown = new bootstrap.Dropdown(document.getElementById('notificationDropdown'));
                                dropdown.hide();
                            }
                        });
                    });

                    // Show divider and mark all read button if there are notifications
                    document.getElementById('notificationDivider').style.display = 'block';
                    if (data.unread_count > 0) {
                        document.getElementById('markAllRead').style.display = 'block';
                    } else {
                        document.getElementById('markAllRead').style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Error loading notifications:', error));
    }

    // Mark notification as read
    function markAsRead(notificationId, link) {
        fetch(`{{ route("notifications.read", ':id') }}`.replace(':id', notificationId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
                if (link && link !== '#') {
                    window.location.href = link;
                }
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    // Load notifications on page load
    loadNotifications();

    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);

    // Mark all as read
    document.getElementById('markAllRead').addEventListener('click', function(e) {
        e.preventDefault();
        fetch('{{ route("notifications.mark.all.read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking all notifications as read:', error));
    });
});
</script>

