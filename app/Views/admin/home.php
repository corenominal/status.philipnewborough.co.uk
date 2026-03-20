<?= $this->extend('templates/dashboard') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            
            <div class="border-bottom border-1 mb-4 pb-4 d-flex align-items-center justify-content-between gap-3">
                <h2 class="mb-0">Admin Home</h2>
                <div class="" role="group" aria-label="Page actions">
                    <button type="button" class="btn btn-outline-primary"><i class="bi bi-plus-circle-fill"></i><span class="d-none d-lg-inline"> New</span></button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary dropdown-toggle" id="btn-status-filter" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-funnel-fill"></i><span class="d-none d-lg-inline"> Status: All</span></button>
                        <ul class="dropdown-menu" aria-labelledby="btn-status-filter">
                            <li><a class="dropdown-item status-filter-item active" href="#" data-value="">All</a></li>
                            <li><a class="dropdown-item status-filter-item" href="#" data-value="Active">Active</a></li>
                            <li><a class="dropdown-item status-filter-item" href="#" data-value="Inactive">Inactive</a></li>
                            <li><a class="dropdown-item status-filter-item" href="#" data-value="Banned">Banned</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-outline-primary" id="btn-datatable-refresh"><i class="bi bi-arrow-clockwise"></i><span class="d-none d-lg-inline"> Refresh</span></button>
                    <button type="button" class="btn btn-outline-danger" id="btn-delete" disabled><i class="bi bi-trash3-fill"></i><span class="d-none d-lg-inline"> Delete</span></button>
                </div>
            </div>

            <p>This is an example table with some sample data.</p>

            <div class="table-responsive">
                <table id="example-table" class="table table-bordered table-striped table-hover align-middle" style="width:100%">
                    <thead>
                        <tr>
                            <th></th>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- Delete confirmation modal -->
<div class="modal fade" id="modal-delete-confirm" tabindex="-1" aria-labelledby="modal-delete-confirm-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-delete-confirm-label">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <strong id="delete-modal-count">0</strong> selected record(s)? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btn-delete-confirm">Delete</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>