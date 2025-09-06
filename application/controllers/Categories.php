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

    public function get_categories()
    {
        try {
            $categories = $this->Categories_model->get_all();
            $data = [];
            $no = 1;
            foreach ($categories as $cat) {
                $row = [];
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
        if ($this->input->server('REQUEST_METHOD') === 'POST') {

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
                echo json_encode([
                    'status'  => 'error',
                    'message' => '<ul>' . validation_errors('<li>', '</li>') . '</ul>'
                ]);
                return;
            }

            $data = [
                'category_name' => $this->input->post('category_name', TRUE),
                'description'   => $this->input->post('description', TRUE),
                'created_at'    => date('Y-m-d H:i:s')

            ];

            try {
                $operation = $this->Categories_model->insert($data);

                echo json_encode([
                    'status'  => $operation ? 'success' : 'error',
                    'message' => $operation ? 'Category saved successfully!' : 'Failed to save category.'
                ]);
            } catch (Exception $e) {
                log_message('error', 'Save Categories Error: ' . $e->getMessage());
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Unexpected error: ' . $e->getMessage()
                ]);
            }
        } else {
            show_error('No direct script access allowed');
        }
    }

    public function update_categories()
    {
        if ($this->input->server('REQUEST_METHOD') === 'POST') {

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
                'required|is_unique[categories.category_name]',
                [
                    'required'  => 'Category name is required',
                    'is_unique' => 'This category already exists'
                ]
            );

            if ($this->form_validation->run() == FALSE) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => '<ul>' . validation_errors('<li>', '</li>') . '</ul>'
                ]);
                return;
            }

            $category_id = $this->input->post('id', TRUE);
            $data = [
                'category_name' => $this->input->post('category_name', TRUE),
                'description'   => $this->input->post('description', TRUE),
                'updated_at'    => date('Y-m-d H:i:s')
            ];

            try {
                $operation = $this->Categories_model->update($category_id, $data);

                echo json_encode([
                    'status'  => $operation ? 'success' : 'error',
                    'message' => $operation ? 'Category updated successfully!' : 'Failed to update category.'
                ]);
            } catch (Exception $e) {
                log_message('error', 'Update Categories Error: ' . $e->getMessage());
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Unexpected error: ' . $e->getMessage()
                ]);
            }
        } else {
            show_error('No direct script access allowed');
        }
    }

    public function delete_categories()
    {
        if ($this->input->server('REQUEST_METHOD') === 'POST') {

            $this->form_validation->set_rules(
                'id',
                'Category ID',
                'required',
                [
                    'required'  => 'Id is required'
                ]
            );

            if ($this->form_validation->run() == FALSE) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => '<ul>' . validation_errors('<li>', '</li>') . '</ul>'
                ]);
                return;
            }

            $category_id = $this->input->post('id', TRUE);

            try {
                $operation = $this->Categories_model->delete($category_id);

                echo json_encode([
                    'status'  => $operation ? 'success' : 'error',
                    'message' => $operation ? 'Category deleted successfully!' : 'Failed to delete category.'
                ]);
            } catch (Exception $e) {
                log_message('error', 'Delete Categories Error: ' . $e->getMessage());
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Unexpected error: ' . $e->getMessage()
                ]);
            }
        } else {
            show_error('No direct script access allowed');
        }
    }

    public function upload_categories()
    {
        $response = ['status' => 'error', 'message' => ''];

        if (isset($_FILES['upload_file']['name'])) {
            $file = $_FILES['upload_file']['tmp_name'];

            $spreadsheet = IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();

            $data = [];
            $errors = [];

            foreach ($sheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = trim($cell->getValue());
                }

                $this->form_validation->reset_validation();
                $_POST['category_name'] = $rowData[0];
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
                    $errors[] = "Row " . $row->getRowIndex() . ": " . validation_errors('', '');
                } else {
                    $data[] = [
                        'category_name' => $rowData[0],
                        'description'   => isset($rowData[1]) ? $rowData[1] : null,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ];
                }
            }

            if (!empty($errors)) {
                $response['message'] = implode('<br>', $errors);
            } else {
                $this->Categories_model->insert_batch($data);
                $response['status'] = 'success';
                $response['message'] = 'All categories imported successfully.';
            }
        } else {
            $response['message'] = 'No file uploaded.';
        }

        echo json_encode($response);
    }
}
