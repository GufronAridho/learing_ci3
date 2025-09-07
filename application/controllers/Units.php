<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Units_model $Units_model
 * @property CI_Security $security
 */

class Units extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Units_model');
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

    public function get_units()
    {
        try {
            $table = $this->Units_model->get_all();
            $data = [];
            $no = 1;
            foreach ($table as $td) {
                $row = [];
                $row['checkbox'] = '<input type="checkbox" class="delete-checkbox" value="' . $td->id . '">';
                $row['no'] = $no++;
                $row['unit_name'] = $td->unit_name;
                $row['description'] = $td->description;
                $row['action'] = '
                <button class="btn btn-sm btn-warning edit-btn" data-id="' . $td->id . '" data-unit_name="' . $td->unit_name . '" data-description="' . $td->description . '">
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

    public function save_units()
    {
        $this->form_validation->set_rules(
            'unit_name',
            'Unit Name',
            'required|is_unique[units.unit_name]',
            [
                'required'  => 'Unit name is required',
                'is_unique' => 'This Unit already exists'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'unit_name' => $this->input->post('unit_name', TRUE),
            'description'   => $this->input->post('description', TRUE),
            'created_at'    => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Units_model->insert($data);
            $msg = $operation ? 'Unit saved successfully!' : 'Failed to save Unit.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Save Unit Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function _unique_unit_name($name, $id)
    {
        $exists = $this->db->where('unit_name', $name)
            ->where('id !=', $id)
            ->get('units')
            ->row();

        return $exists ? FALSE : TRUE;
    }

    public function update_units()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Unit ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );
        $this->form_validation->set_rules(
            'unit_name',
            'Unit name',
            'required[callback__unique_unit_name[' . $id . ']',
            [
                'required' => 'Unit name is required',
                '_unique_unit_name' => 'This unit already exist'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'unit_name' => $this->input->post('unit_name', TRUE),
            'description' => $this->input->post('description', TRUE),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Units_model->update($id, $data);
            $msg = $operation ? 'Unit updated successfully!' : 'Failed to update unit.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Update Unit Error' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }
    public function delete_units()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Unit ID',
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
            $operation = $this->Units_model->delete($id);
            $msg = $operation ? 'Unit deleted successfully!' : 'Failed to delete unit.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Delete Unit Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function upload_units()
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
                    'unit_name',
                    'Unit Name',
                    'required|is_unique[units.unit_name]',
                    [
                        'required'  => 'Unit name is required',
                        'is_unique' => 'This Unit already exists'
                    ]
                );

                if ($this->form_validation->run() == FALSE) {
                    $errors[] = "Row {$rowIndex}: " . validation_errors('', '');
                } else {
                    $data[] = [
                        'unit_name' => $rowData[0],
                        'description' => $rowData[1] ?? null,
                        'created_at' => date('Y-m-d H:i:s'),
                    ];
                }
            }

            if (!empty($errors)) {
                $this->_json_response('error', implode('<br>', $errors));
            } else {
                $this->Units_model->insert_batch($data);
                $inserted = count($data);
                $this->_json_response('success', "Successfully inserted {$inserted} unit.");
            }
        } catch (Exception $e) {
            log_message('error', 'Upload Unit Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_multiple_units()
    {
        $ids = $this->input->post('ids');

        if (empty($ids)) {
            $this->_json_response('error', 'No unit selected');
            return;
        }

        try {
            $deleted = $this->Units_model->delete_multiple($ids);

            if ($deleted > 0) {
                $this->_json_response('success', "Deleted {$deleted} categories successfully.");
            } else {
                $this->_json_response('error', 'No categories were deleted. IDs may not exist.');
            }
        } catch (Exception $e) {
            log_message('error', 'Delete Cheklist Unit Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }
}
