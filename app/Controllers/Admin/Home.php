<?php

namespace App\Controllers\Admin;

use Hermawan\DataTables\DataTable;
use App\Models\ExampleModel;

class Home extends BaseController
{
    /**
     * Display the Admin Dashboard page.
     *
     * Prepares view data for the dashboard, including:
     * - Datatables feature flag
     * - JavaScript asset list
     * - CSS asset list
     * - Page title
     *
     * @return string Rendered admin dashboard view output.
     */
    public function index()
    {
        // Datatables flag
        $data['datatables'] = true;
        // Array of javascript files to include
        $data['js'] = ['admin/home'];
        // Array of CSS files to include
        $data['css'] = ['admin/home'];
        // Set the page title
        $data['title'] = 'Admin Dashboard';    
        return view('admin/home', $data);
    }

    /**
     * Server-side DataTables endpoint for the example table.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface JSON response for DataTables.
     */
    public function datatable()
    {
        $model   = new ExampleModel();
        $builder = $model->builder()->where('deleted_at IS NULL');

        $statusFilter = $this->request->getGet('status_filter');
        if (!empty($statusFilter)) {
            $builder->where('status', $statusFilter);
        }

        $statusMap = [
            'Active'   => 'success',
            'Inactive' => 'warning',
            'Banned'   => 'danger',
        ];

        return DataTable::of($builder)
            ->edit('status', function($row) use ($statusMap) {
                $colour = $statusMap[$row->status] ?? 'secondary';
                return '<span class="badge text-bg-' . $colour . '">' . esc($row->status) . '</span>';
            })
            ->toJson(true);
    }

    /**
     * Delete selected records (soft delete).
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete()
    {
        $json = $this->request->getJSON(true);
        $ids  = $json['ids'] ?? [];

        // Sanitise: keep only positive integers
        $ids = array_values(array_filter(array_map('intval', $ids), fn($id) => $id > 0));

        if (empty($ids)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'No valid IDs provided.',
            ]);
        }

        $model = new ExampleModel();
        $model->whereIn('id', $ids)->delete();

        return $this->response->setJSON([
            'status'  => 'success',
            'deleted' => count($ids),
        ]);
    }
}
