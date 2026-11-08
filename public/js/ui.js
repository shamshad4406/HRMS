/* Innobles HRMS - shared UI behaviour (sidebar drawer, responsive tables) */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        var body = document.body;
        var sidebar = document.querySelector('.sidebar');
        var toggle = document.querySelector('.menu-toggle');
        var backdrop = document.querySelector('.sidebar-backdrop');
        var closeBtn = document.querySelector('.sidebar-close');

        function openSidebar() { body.classList.add('sidebar-open'); if (toggle) toggle.setAttribute('aria-expanded', 'true'); }
        function closeSidebar() { body.classList.remove('sidebar-open'); if (sidebar) sidebar.classList.remove('show'); if (toggle) toggle.setAttribute('aria-expanded', 'false'); }

        if (sidebar && toggle) {
            toggle.addEventListener('click', function () {
                body.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
            });
        }
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeSidebar(); });
        window.addEventListener('resize', function () { if (window.innerWidth > 991) closeSidebar(); });
        if (sidebar) {
            sidebar.querySelectorAll('a').forEach(function (a) {
                a.addEventListener('click', function () { if (window.innerWidth <= 991) closeSidebar(); });
            });
        }

        // Responsive tables: copy header text into each cell so rows can
        // turn into stacked cards on phones (see .table-stack in theme.css)
        document.querySelectorAll('table.table').forEach(function (table) {
            if (table.classList.contains('no-stack') || table.closest('.no-stack')) return;
            var head = table.querySelector('thead tr');
            if (!head) return;
            var labels = Array.prototype.map.call(head.children, function (th) { return th.textContent.trim(); });
            table.querySelectorAll('tbody tr, tfoot tr').forEach(function (tr) {
                Array.prototype.forEach.call(tr.children, function (td, i) {
                    if (td.tagName === 'TD' && !td.hasAttribute('colspan')) td.setAttribute('data-label', labels[i] || '');
                });
            });
            table.classList.add('table-stack');
        });

        // Auto-hide success and error alerts after 2 seconds
        document.querySelectorAll('.alert-success, .alert-danger').forEach(function (alert) {
            setTimeout(function () {
                alert.classList.remove('show');
                setTimeout(function () {
                    alert.remove();
                }, 150);
            }, 5000);
        });
    });
})();
