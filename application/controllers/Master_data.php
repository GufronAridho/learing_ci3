<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Categories_model $Categories_model
 */

class Master_data extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Categories_model');
        $this->load->helper('url');
    }

    public function index()
    {
        $this->load->database();

        if ($this->db->conn_id) {
            echo "✅ Database connected!";
        } else {
            echo "❌ Database connection failed!";
        }
    }

    public function user_management()
    {
        $data['title'] = 'User Management';
        $data['contents'] = $this->load->view('master_data/user_management', [], TRUE);

        $this->load->view('layouts/main', $data);
    }

    public function products()
    {
        $data['title'] = 'Product';
        $data['contents'] = $this->load->view('master_data/products', [], TRUE);

        $this->load->view('layouts/main', $data);
    }


    public function units()
    {
        $data['title'] = 'Units';
        $data['contents'] = $this->load->view('master_data/units', [], TRUE);

        $this->load->view('layouts/main', $data);
    }

    public function suppliers()
    {
        $data['title'] = 'Suppliers';
        $data['contents'] = $this->load->view('master_data/suppliers', [], TRUE);

        $this->load->view('layouts/main', $data);
    }

    public function customers()
    {
        $data['title'] = 'Customers';
        $data['contents'] = $this->load->view('master_data/customers', [], TRUE);

        $this->load->view('layouts/main', $data);
    }




    // CATEGORIES //
    public function categories()
    {
        $data['title'] = 'Categories';
        // $data['results']  = $this->Categories_model->get_all();
        $data['contents'] = $this->load->view('master_data/categories', [], TRUE);

        $this->load->view('layouts/main', $data);
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
                $insert = $this->Categories_model->insert($data);

                if ($insert) {
                    echo json_encode([
                        'status'  => 'success',
                        'message' => 'Category saved successfully!'
                    ]);
                } else {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Database insert failed.'
                    ]);
                }
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
                'required'
            );
            $this->form_validation->set_rules(
                'category_name',
                'Category Name',
                'required'
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
                $update = $this->Categories_model->update($category_id, $data);

                if ($update) {
                    echo json_encode([
                        'status'  => 'success',
                        'message' => 'Category updated successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Failed to update category.'
                    ]);
                }
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
                'required'
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
                $update = $this->Categories_model->delete($category_id);

                if ($update) {
                    echo json_encode([
                        'status'  => 'success',
                        'message' => 'Category deleted successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'status'  => 'error',
                        'message' => 'Failed to delete category.'
                    ]);
                }
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
}
