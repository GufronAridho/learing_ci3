<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Categories_model $Categories_model
 * @property CI_Security $security
 */

class Categories extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Categories_model');
        $this->load->helper('url');
    }

    private function _json_response($status, $message)
    {
        echo json_encode([
            'status'    => $status,
            'message'   => $message
            // ,'csrf_name' => $this->security->get_csrf_token_name(),
            // 'csrf_hash' => $this->security->get_csrf_hash()
        ]);
        exit;
    }

    public function get_categories()
    {
        try {
            $categories = $this->Categories_model->get_all();
            $data = [];
            $no = 1;
            foreach ($categories as $cat) {
                $row = [];
                $row['checkbox'] = '<input type="checkbox" class="delete-checkbox" value="' . $cat->id . '">';
                $row['no'] = $no++;
                $row['category_name'] = $cat->category_name;
                $row['description'] = $cat->description;
                $row['action'] = '
                <button class="btn btn-sm btn-warning edit-btn" data-id="' . $cat->id . '" data-category_name="' . $cat->category_name . '" data-description="' . $cat->description . '">
                    <i class="bi bi-pencil-square"></i>
                </button>
                <button class="btn btn-sm btn-danger delete-btn" data-id="' . $cat->id . '">
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

    public function save_categories()
    {
        $this->form_validation->set_rules(
            'category_name',
            'Category Name',
            'required|is_unique[categories.category_name]',
            [
                'required'  => 'Category name is required',
                'is_unique' => 'This category already exists'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'category_name' => $this->input->post('category_name', TRUE),
            'description'   => $this->input->post('description', TRUE),
            'created_at'    => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Categories_model->insert($data);
            $msg = $operation ? 'Category saved successfully!' : 'Failed to save category.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Save Categories Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function _unique_category_name($name, $id)
    {
        $exists = $this->db->where('category_name', $name)
            ->where('id !=', $id)
            ->get('categories')
            ->row();

        return $exists ? FALSE : TRUE;
    }

    public function update_categories()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Category ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );
        $this->form_validation->set_rules(
            'category_name',
            'Category Name',
            'required|callback__unique_category_name[' . $id . ']',
            [
                'required'  => 'Category name is required',
                '_unique_category_name' => 'This category already exists'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'category_name' => $this->input->post('category_name', TRUE),
            'description'   => $this->input->post('description', TRUE),
            'updated_at'    => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Categories_model->update($id, $data);
            $msg = $operation ? 'Category updated successfully!' : 'Failed to update category.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Update Categories Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_categories()
    {
        $this->form_validation->set_rules(
            'id',
            'Category ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $id = $this->input->post('id', TRUE);

        try {
            $operation = $this->Categories_model->delete($id);
            $msg = $operation ? 'Category deleted successfully!' : 'Failed to delete category.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Delete Categories Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function upload_categories()
    {
        if (!isset($_FILES['upload_file']['name']) || empty($_FILES['upload_file']['name'])) {
            $this->_json_response('error', 'No file uploaded.');
            return;
        }

        $allowed_ext = ['xlsx'];
        $file_name   = $_FILES['upload_file']['name'];
        $ext         = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

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

                $this->form_validation->reset_validation();
                $_POST['category_name'] = $rowData[0] ?? '';

                $this->form_validation->set_rules(
                    'category_name',
                    'Category Name',
                    'required|is_unique[categories.category_name]',
                    [
                        'required'  => 'Category name is required',
                        'is_unique' => 'This category already exists'
                    ]
                );

                if ($this->form_validation->run() === FALSE) {
                    $errors[] = "Row {$rowIndex}: " . validation_errors('', '');
                } else {
                    $data[] = [
                        'category_name' => $rowData[0],
                        'description'   => $rowData[1] ?? null,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ];
                }
            }

            if (!empty($errors)) {
                $this->_json_response('error', implode('<br>', $errors));
            } else {
                $this->Categories_model->insert_batch($data);
                $inserted = count($data);
                $this->_json_response('success', "Successfully inserted {$inserted} categories.");
            }
        } catch (Exception $e) {
            log_message('error', 'Upload Categories Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_multiple_categories()
    {
        $ids = $this->input->post('ids');

        if (empty($ids)) {
            $this->_json_response('error', 'No categories selected.');
            return;
        }

        try {
            $deleted = $this->Categories_model->delete_multiple($ids);

            if ($deleted > 0) {
                $this->_json_response('success', "Deleted {$deleted} categories successfully.");
            } else {
                $this->_json_response('error', 'No categories were deleted. IDs may not exist.');
            }
        } catch (Exception $e) {
            $this->_json_response('error', $e->getMessage());
        }
    }
}
