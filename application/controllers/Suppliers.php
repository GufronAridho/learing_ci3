<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Suppliers_model $Suppliers_model
 * @property CI_Security $security
 */

class Suppliers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Suppliers_model');
        $this->load->helper('url');
    }

    private function _json_response($status, $message)
    {
        echo json_encode([
            'status'    => $status,
            'message'   => $message
        ]);
        exit;
    }

    public function get_suppliers()
    {
        try {
            $table = $this->Suppliers_model->get_all();
            $data = [];
            $row = 1;

            foreach ($table as $td) {
                $row = [];
                $row['checklist'] = '<input type="checkbox" class="delete-checkbox" value="' . $td->id . '">';
                $row['supplier_code'] = $td->supplier_code;
                $row['supplier_name'] = $td->supplier_name;
                $row['contact_name'] = $td->contact_name;
                $row['phone'] = $td->phone;
                $row['email'] = $td->email;
                $row['address'] = $td->address;
                $row['action'] = '
                <button class="btn btn-sm btn-warning edit-btn" data-id="' . $td->id . '" data-supplier_code="' . $td->supplier_code . '" data-supplier_name="' . $td->supplier_name . '" 
                data-contact_name="' . $td->contact_name . '" data-phone="' . $td->phone . '" data-email="' . $td->email . '" data-address="' . $td->address . '">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="' . $td->id . '">
                    <i class="bi bi-trash"></i>
                </button>
                ';
                $data[] = $row;
            }
            echo json_encode([
                'status' => 'success',
                'data'   => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function save_suppliers()
    {
        $this->form_validation->set_rules(
            'supplier_code',
            'Supplier Code',
            'required|is_unique[suppliers.supplier_code]',
            [
                'required'  => 'Supplier code is required',
                'is_unique' => 'This Supplier code already exists'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'supplier_code' => $this->input->post('supplier_code', TRUE),
            'supplier_name' => $this->input->post('supplier_name', TRUE),
            'contact_name' => $this->input->post('contact_name', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'email' => $this->input->post('email', TRUE),
            'address' => $this->input->post('address', TRUE),
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Suppliers_model->insert($data);
            $msg = $operation ? 'Supplier saved successfully!' : 'Failed to save supplier.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Save Supplier Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function _unique_suppliers_code($name, $id)
    {
        $exists = $this->db->where('supplier_code', $name)
            ->where('id !=', $id)
            ->get('suppliers')
            ->row();

        return $exists ? FALSE : TRUE;
    }

    public function update_suppliers()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Supplier ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );
        $this->form_validation->set_rules(
            'supplier_code',
            'Supplier Code',
            'required[callback__unique_suppliers_code[' . $id . ']',
            [
                'required' => 'Supplier code is required',
                '_unique_suppliers_code' => 'This Supplier code already exist'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'supplier_code' => $this->input->post('supplier_code', TRUE),
            'supplier_name' => $this->input->post('supplier_name', TRUE),
            'contact_name' => $this->input->post('contact_name', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'email' => $this->input->post('email', TRUE),
            'address' => $this->input->post('address', TRUE),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Suppliers_model->update($id, $data);
            $msg = $operation ? 'Supplier updated successfully!' : 'Failed to update Supplier.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Update Supplier Error' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_suppliers()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Supplier ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        try {
            $operation = $this->Suppliers_model->delete($id);
            $msg = $operation ? 'Supplier deleted successfully!' : 'Failed to delete Supplier.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Delete Supplier Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function upload_suppliers()
    {
        if (!isset($_FILES['upload_file']['name']) || empty($_FILES['upload_file']['name'])) {
            $this->_json_response('error', 'No file uploadede');
            return;
        }

        $allowed_ext = ['xlsx'];
        $file_name = $_FILES['upload_file']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $this->_json_response('error', 'Invalid file type. Only .xlsx files are allowed.');
            return;
        }

        try {
            $file = $_FILES['upload_file']['tmp_name'];
            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();

            $data = [];
            $errors = [];
            $rowIndex = 1;

            foreach ($sheet->getRowIterator(2) as $row) {
                $rowIndex++;
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = trim($cell->getValue());
                }

                $this->form_validation->set_rules(
                    'supplier_code',
                    'Supplier Code',
                    'required|is_unique[suppliers.supplier_code]',
                    [
                        'required'  => 'Supplier code is required',
                        'is_unique' => 'This Supplier code already exists'
                    ]
                );
                $this->form_validation->set_rules(
                    'supplier_name',
                    'Supplier Name',
                    'required',
                    [
                        'required'  => 'Supplier name is required',
                    ]
                );

                if ($this->form_validation->run() == FALSE) {
                    $errors[] = "Row {$rowIndex}: " . validation_errors('', '');
                } else {
                    $data[] = [
                        'supplier_code' => $rowData[0],
                        'supplier_name' => $rowData[1],
                        'contact_name' => $rowData[2] ?? null,
                        'phone' => $rowData[3] ?? null,
                        'email' => $rowData[4] ?? null,
                        'address' => $rowData[4] ?? null,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
            if (!empty($errors)) {
                $this->_json_response('error', implode('<br>', $errors));
            } else {
                $this->Suppliers_model->insert_batch($data);
                $inserted = count($data);
                $this->_json_response('success', "Successfully inserted {$inserted} Supplier.");
            }
        } catch (Exception $e) {
            log_message('error', 'Upload Supplier Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_multiple_suppliers()
    {
        $ids = $this->input->post('ids');

        if (empty($ids)) {
            $this->_json_response('error', 'No Supplier selected');
            return;
        }

        try {
            $deleted = $this->Suppliers_model->delete_multiple($ids);

            if ($deleted > 0) {
                $this->_json_response('success', "Deleted {$deleted} Supplier successfully.");
            } else {
                $this->_json_response('error', 'No Supplier were deleted. IDs may not exist.');
            }
        } catch (Exception $e) {
            log_message('error', 'Delete Cheklist Supplier Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }
}
