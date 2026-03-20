document.addEventListener("DOMContentLoaded", function() {
    const sidebarLinks = document.querySelectorAll("#sidebar .nav-link");
    sidebarLinks.forEach(link => {
        if (link.getAttribute("href") === "/admin") {
            link.classList.remove("text-white-50");
            link.classList.add("active");
        }
    });
});

document.addEventListener("DOMContentLoaded", function() {

    // ── Selection state ────────────────────────────────────────────────────────
    const selectedIds = new Set();

    function updateDeleteButton() {
        document.getElementById('btn-delete').disabled = selectedIds.size === 0;
    }

    // ── Status filter state ────────────────────────────────────────────────────
    let statusFilter = '';

    const exampleTable = new DataTable('#example-table', {

        // ── Layout & UI ────────────────────────────────────────────────────────
        autoWidth:      true,           // Auto-calculate column widths
        info:           true,           // Show "Showing X to Y of Z entries"
        lengthChange:   true,           // Allow user to change page length
        ordering:       true,           // Enable column sorting
        paging:         true,           // Enable pagination
        searching:      true,           // Enable global search box
        orderMulti:     true,           // Allow multi-column sort (shift+click)
        orderClasses:   true,           // Add sorting CSS classes to columns
        pagingType:     'simple_numbers', // 'simple' | 'simple_numbers' | 'full' | 'full_numbers' | 'first_last_numbers'
        pageLength:     10,             // Rows per page
        lengthMenu:     [10, 25, 50, 100], // Page length options

        // ── Default sort ───────────────────────────────────────────────────────
        order: [[1, 'asc']],            // [[columnIndex, 'asc'|'desc'], ...]

        // ── Performance ────────────────────────────────────────────────────────
        deferRender:    false,          // Defer rendering off-screen rows (useful for large datasets)
        processing:     true,           // Show a processing indicator (useful with serverSide)
        serverSide:     true,           // Enable server-side processing (requires ajax option)
        stateSave:      false,          // Persist state (paging, sorting, search) in sessionStorage

        // ── Data source ────────────────────────────────────────────────────────
        ajax: {
            url: '/admin/datatable',
            data: function(d) {
                d.status_filter = statusFilter;
            },
        },
        // data: [],                    // Inline JS data array (alternative to HTML or ajax)

        // ── Scroll ─────────────────────────────────────────────────────────────
        scrollX:        false,          // Horizontal scrolling
        scrollY:        '',             // Vertical scroll height, e.g. '400px'
        scrollCollapse: false,          // Shrink table when fewer rows than scrollY height

        // ── Column definitions ─────────────────────────────────────────────────
        columns: [
            {
                // Column 0 — Checkbox (row select)
                data:           null,
                title:          '<input type="checkbox" id="select-all-checkbox" class="form-check-input" aria-label="Select all rows on this page">',
                orderable:      false,
                searchable:     false,
                visible:        true,
                width:          '2rem',
                className:      'text-center',
                defaultContent: '<input type="checkbox" class="row-select form-check-input" aria-label="Select row">',
            },
            {
                // Column 1 — #
                name:        'id',
                data:        'id',
                title:       '#',
                type:        'num',         // 'string' | 'num' | 'num-fmt' | 'html' | 'html-num' | 'date'
                orderable:   true,
                searchable:  false,         // No value in searching the row number
                visible:     true,
                width:       '3rem',
                className:   'text-end',
            },
            {
                // Column 1 — First Name
                name:        'first_name',
                data:        'first_name',
                title:       'First Name',
                type:        'string',
                orderable:   true,
                searchable:  true,
                visible:     true,
                width:       '',            // Leave empty to let autoWidth decide
                className:   '',
            },
            {
                // Column 2 — Last Name
                name:        'last_name',
                data:        'last_name',
                title:       'Last Name',
                type:        'string',
                orderable:   true,
                searchable:  true,
                visible:     true,
                width:       '',
                className:   '',
            },
            {
                // Column 3 — Email
                name:        'email',
                data:        'email',
                title:       'Email',
                type:        'string',
                orderable:   true,
                searchable:  true,
                visible:     true,
                width:       '',
                className:   '',
            },
            {
                // Column 4 — Role
                name:        'role',
                data:        'role',
                title:       'Role',
                type:        'string',
                orderable:   true,
                searchable:  true,
                visible:     true,
                width:       '',
                className:   '',
            },
            {
                // Column 5 — Status (server returns badge HTML; sorting/searching handled server-side on the raw value)
                name:        'status',
                data:        'status',
                title:       'Status',
                type:        'string',
                orderable:   true,
                searchable:  true,
                visible:     true,
                width:       '6rem',
                className:   'text-center',
            },
            {
                // Column 6 — Joined (ISO date string sorts correctly as a string)
                name:        'joined',
                data:        'joined',
                title:       'Joined',
                type:        'date',
                orderable:   true,
                searchable:  false,
                visible:     true,
                width:       '7rem',
                className:   '',
            },
        ],

        // ── Language / localisation ────────────────────────────────────────────
        language: {
            emptyTable:     'No data available in table',
            info:           'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty:      'Showing 0 to 0 of 0 entries',
            infoFiltered:   '(filtered from _MAX_ total entries)',
            lengthMenu:     'Show _MENU_ entries',
            loadingRecords: 'Loading...',
            processing:     'Processing...',
            search:         'Search:',
            zeroRecords:    'No matching records found',
            paginate: {
                first:    'First',
                last:     'Last',
                next:     'Next',
                previous: 'Previous',
            },
        },

        // ── Callbacks ──────────────────────────────────────────────────────────
        // initComplete: function(settings, json) {},   // Fires once table is fully initialised
        drawCallback: function() {
            // Restore checkbox state and row highlight for every visible row after each draw
            exampleTable.rows({ page: 'current' }).every(function() {
                const id       = this.data().id;
                const checkbox = this.node().querySelector('.row-select');
                const selected = selectedIds.has(id);
                if (checkbox) checkbox.checked = selected;
                this.node().classList.toggle('table-active', selected);
            });
            // Sync the select-all header checkbox
            const selectAll = document.getElementById('select-all-checkbox');
            if (selectAll) {
                const visibleIds = [];
                exampleTable.rows({ page: 'current' }).every(function() { visibleIds.push(this.data().id); });
                const n = visibleIds.filter(id => selectedIds.has(id)).length;
                selectAll.checked       = n > 0 && n === visibleIds.length;
                selectAll.indeterminate = n > 0 && n <  visibleIds.length;
            }
            updateDeleteButton();
        },
        // rowCallback:  function(row, data, index) {}, // Fires for each row on every draw
        // createdRow:   function(row, data, index) {}, // Fires once per row when the TR element is created
        // headerCallback: function(thead, data, start, end, display) {},

    });

    // ── Row checkbox clicks ────────────────────────────────────────────────────
    document.querySelector('#example-table tbody').addEventListener('change', function(e) {
        if (!e.target.classList.contains('row-select')) return;
        const row = exampleTable.row(e.target.closest('tr'));
        const id  = row.data().id;
        const tr  = e.target.closest('tr');
        if (e.target.checked) {
            selectedIds.add(id);
            tr.classList.add('table-active');
        } else {
            selectedIds.delete(id);
            tr.classList.remove('table-active');
        }
        // Sync the select-all header checkbox
        const selectAll = document.getElementById('select-all-checkbox');
        if (selectAll) {
            const visibleIds = [];
            exampleTable.rows({ page: 'current' }).every(function() { visibleIds.push(this.data().id); });
            const n = visibleIds.filter(id => selectedIds.has(id)).length;
            selectAll.checked       = n > 0 && n === visibleIds.length;
            selectAll.indeterminate = n > 0 && n <  visibleIds.length;
        }
        updateDeleteButton();
    });

    // ── Select-all checkbox (current page) ────────────────────────────────────
    document.querySelector('#example-table thead').addEventListener('change', function(e) {
        if (e.target.id !== 'select-all-checkbox') return;
        exampleTable.rows({ page: 'current' }).every(function() {
            const id       = this.data().id;
            const checkbox = this.node().querySelector('.row-select');
            if (e.target.checked) {
                selectedIds.add(id);
                if (checkbox) checkbox.checked = true;
                this.node().classList.add('table-active');
            } else {
                selectedIds.delete(id);
                if (checkbox) checkbox.checked = false;
                this.node().classList.remove('table-active');
            }
        });
        updateDeleteButton();
    });

    // ── Status filter dropdown ──────────────────────────────────────────────────
    document.querySelectorAll('.status-filter-item').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const value = this.dataset.value;
            // Update active state on dropdown items
            document.querySelectorAll('.status-filter-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            // Update button label
            const label = value || 'All';
            document.getElementById('btn-status-filter').innerHTML =
                '<i class="bi bi-funnel-fill"></i><span class="d-none d-lg-inline"> Status: ' + label + '</span>';
            // Send filter as a custom AJAX param; server applies an exact WHERE clause
            statusFilter = value;
            exampleTable.ajax.reload(null, false);
        });
    });

    // ── Refresh button ─────────────────────────────────────────────────────────
    document.getElementById('btn-datatable-refresh').addEventListener('click', function() {
        exampleTable.ajax.reload(null, false); // null keeps current page; false = don't reset paging
        console.log('Table refreshed');
    });

    // ── Delete button → show confirmation modal ────────────────────────────────
    const deleteModalEl = document.getElementById('modal-delete-confirm');
    // Disable Bootstrap's built-in FocusTrap (focus: false) so we can manage
    // focus ourselves. Without this, the trap fights focus-move attempts made
    // during the hide.bs.modal event and snaps focus back inside the modal —
    // causing the "Blocked aria-hidden on a focused element" warning.
    const deleteModal   = new bootstrap.Modal(deleteModalEl, { focus: false });

    // When the modal finishes opening, focus the close button manually
    // (replaces the behaviour normally provided by Bootstrap's FocusTrap).
    deleteModalEl.addEventListener('shown.bs.modal', function() {
        const closeBtn = deleteModalEl.querySelector('.btn-close');
        if (closeBtn) closeBtn.focus();
    });

    // Move focus outside the modal before Bootstrap sets aria-hidden.
    // Because FocusTrap is disabled, nothing fights this move, so focus is
    // guaranteed to be outside the modal when aria-hidden="true" is applied
    // after the fade animation completes.
    deleteModalEl.addEventListener('hide.bs.modal', function() {
        const focused = deleteModalEl.querySelector(':focus');
        if (focused) focused.blur();
        const btn = document.getElementById('btn-delete');
        if (btn && !btn.disabled) btn.focus();
    });

    document.getElementById('btn-delete').addEventListener('click', function() {
        document.getElementById('delete-modal-count').textContent = selectedIds.size;
        deleteModal.show();
    });

    // ── Confirm delete ─────────────────────────────────────────────────────────
    document.getElementById('btn-delete-confirm').addEventListener('click', function() {
        const ids = Array.from(selectedIds);

        fetch('/admin/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids }),
        })
        .then(res => res.json())
        .then(() => {
            deleteModal.hide();
            selectedIds.clear();
            updateDeleteButton();
            exampleTable.ajax.reload(null, false);
        })
        .catch(err => console.error('Delete failed:', err));
    });

});

