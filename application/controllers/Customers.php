<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Customers_model $Customers_model
 * @property CI_Security $security
 */

class Customers extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Customers_model');
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

    public function get_customers()
    {
        try {
            $table = $this->Customers_model->get_all();
            $data = [];
            $row = 1;

            foreach ($table as $td) {
                $row = [];
                $row['checklist'] = '<input type="checkbox" class="delete-checkbox" value="' . $td->id . '">';
                $row['customer_code'] = $td->customer_code;
                $row['customer_name'] = $td->customer_name;
                $row['contact_name'] = $td->contact_name;
                $row['phone'] = $td->phone;
                $row['email'] = $td->email;
                $row['address'] = $td->address;
                $row['action'] = '
                <button class="btn btn-sm btn-warning edit-btn" data-id="' . $td->id . '" data-customer_code="' . $td->customer_code . '" data-customer_name="' . $td->customer_name . '" 
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

    public function save_customers()
    {
        $this->form_validation->set_rules(
            'customer_code',
            'Customer Code',
            'required|is_unique[customers.customer_code]',
            [
                'required'  => 'Customer code is required',
                'is_unique' => 'This Customer code already exists'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'customer_code' => $this->input->post('customer_code', TRUE),
            'customer_name' => $this->input->post('customer_name', TRUE),
            'contact_name' => $this->input->post('contact_name', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'email' => $this->input->post('email', TRUE),
            'address' => $this->input->post('address', TRUE),
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Customers_model->insert($data);
            $msg = $operation ? 'Customer saved successfully!' : 'Failed to save Customer.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Save Customer Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function _unique_customers_code($name, $id)
    {
        $exists = $this->db->where('customer_code', $name)
            ->where('id !=', $id)
            ->get('customers')
            ->row();

        return $exists ? FALSE : TRUE;
    }

    public function update_customers()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Customer ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );
        $this->form_validation->set_rules(
            'customer_code',
            'Customer Code',
            'required[callback__unique_customers_code[' . $id . ']',
            [
                'required' => 'Customer code is required',
                '_unique_customers_code' => 'This Customer code already exist'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'customer_code' => $this->input->post('customer_code', TRUE),
            'customer_name' => $this->input->post('customer_name', TRUE),
            'contact_name' => $this->input->post('contact_name', TRUE),
            'phone' => $this->input->post('phone', TRUE),
            'email' => $this->input->post('email', TRUE),
            'address' => $this->input->post('address', TRUE),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Customers_model->update($id, $data);
            $msg = $operation ? 'Customer updated successfully!' : 'Failed to update Customer.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Update Customer Error' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_customers()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Customer ID',
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
            $operation = $this->Customers_model->delete($id);
            $msg = $operation ? 'Customer deleted successfully!' : 'Failed to delete Customer.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Delete Customer Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function upload_customers()
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
                    'customer_code',
                    'Customer Code',
                    'required|is_unique[customers.customer_code]',
                    [
                        'required'  => 'Customer code is required',
                        'is_unique' => 'This Customer code already exists'
                    ]
                );
                $this->form_validation->set_rules(
                    'customer_name',
                    'Customer Name',
                    'required',
                    [
                        'required'  => 'Customer name is required',
                    ]
                );

                if ($this->form_validation->run() == FALSE) {
                    $errors[] = "Row {$rowIndex}: " . validation_errors('', '');
                } else {
                    $data[] = [
                        'customer_code' => $rowData[0],
                        'customer_name' => $rowData[1],
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
                $this->Customers_model->insert_batch($data);
                $inserted = count($data);
                $this->_json_response('success', "Successfully inserted {$inserted} Customer.");
            }
        } catch (Exception $e) {
            log_message('error', 'Upload Customer Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_multiple_customers()
    {
        $ids = $this->input->post('ids');

        if (empty($ids)) {
            $this->_json_response('error', 'No Customer selected');
            return;
        }

        try {
            $deleted = $this->Customers_model->delete_multiple($ids);

            if ($deleted > 0) {
                $this->_json_response('success', "Deleted {$deleted} Customer successfully.");
            } else {
                $this->_json_response('error', 'No Customer were deleted. IDs may not exist.');
            }
        } catch (Exception $e) {
            log_message('error', 'Delete Cheklist Customer Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }
}
