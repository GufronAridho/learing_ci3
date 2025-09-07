<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Products_model $Products_model
 * @property CI_Security $security
 * @property CI_Upload $upload
 */

class Products extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Products_model');
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

    public function get_products()
    {
        try {
            $table = $this->Products_model->get_all();
            $data = [];
            $no = 1;
            foreach ($table as $td) {
                $row = [];
                $row['checkbox'] = '<input type="checkbox" class="delete-checkbox" value="' . $td->id . '">';
                $row['no'] = $no++;
                $row['product_code'] = $td->product_code;
                $row['product_name'] = $td->product_name;
                $row['category'] = $td->category;
                $row['unit'] = $td->unit;
                $row['price'] = $td->price;
                $row['img_file'] = $td->img_file;
                $row['action'] = '
                <button class="btn btn-sm btn-warning edit-btn" data-id="' . $td->id . '" data-product_code="' . $td->product_code . '" data-product_name="' . $td->product_name . '" 
                data-category="' . $td->category . '" data-unit="' . $td->unit . '" data-price="' . $td->price . '" data-unit="' . $td->unit . '" data-img_file="' . $td->img_file . '">
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

    public function save_products()
    {
        $this->form_validation->set_rules(
            'product_code',
            'Product Code',
            'required|is_unique[products.product_code]',
            [
                'required'  => 'Product code is required',
                'is_unique' => 'This Product code already exists'
            ]
        );
        $this->form_validation->set_rules(
            'product_name',
            'Product Name',
            'required',
            [
                'required'  => 'Product name is required',
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $img_file = null;
        if (!empty($_FILES['img_file']['name'])) {
            $config['upload_path'] = './assets/upload/product';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $config['encrypt_name']  = TRUE;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('img_file')) {
                $uploadData = $this->upload->data();
                $img_file = $uploadData['file_name'];
            } else {
                $this->_json_response('error', $this->upload->display_errors('<li>', '</li>'));
                return;
            }
        }

        $data = [
            'product_code' => $this->input->post('product_code', TRUE),
            'product_name' => $this->input->post('product_name', TRUE),
            'category' => $this->input->post('category', TRUE),
            'unit' => $this->input->post('unit', TRUE),
            'price' => $this->input->post('price', TRUE),
            'img_file' => $img_file,
            'created_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Products_model->insert($data);
            $msg = $operation ? 'Product saved successfully!' : 'Failed to save product.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Save Product Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function _unique_product_code($name, $id)
    {
        $exists = $this->db->where('product_code', $name)
            ->where('id !=', $id)
            ->get('products')
            ->row();

        return $exists ? FALSE : TRUE;
    }

    public function update_products()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Product ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );
        $this->form_validation->set_rules(
            'product_code',
            'Product Code',
            'required[callback__unique_product_code[' . $id . ']',
            [
                'required' => 'Product code is required',
                '_unique_product_code' => 'This product code already exist'
            ]
        );
        $this->form_validation->set_rules(
            'product_name',
            'Product Name',
            'required',
            [
                'required'  => 'Product name is required',
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $product = $this->Products_model->get_by_id($id);

        $img_file = $product->img_file;

        if (!empty($_FILES['img_file']['name'])) {
            $config['upload_path'] = './assets/upload/product/';
            $config['allowed_types'] = 'jpg|jpeg|png|gif';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('img_file')) {
                $uploadData = $this->upload->data();
                $img_file = $uploadData['file_name'];
            } else {
                $this->_json_response('error', $this->upload->display_errors('<li>', '</li>'));
                return;
            }
        }

        $data = [
            'product_code' => $this->input->post('product_code', TRUE),
            'product_name' => $this->input->post('product_name', TRUE),
            'category' => $this->input->post('category', TRUE),
            'unit' => $this->input->post('unit', TRUE),
            'price' => $this->input->post('price', TRUE),
            'img_file' => $img_file,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Products_model->update($id, $data);
            $msg = $operation ? 'Product updated successfully!' : 'Failed to update product.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Update Product Error' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_products()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'Product ID',
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
            $operation = $this->Products_model->delete($id);
            $msg = $operation ? 'Product deleted successfully!' : 'Failed to delete product.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Delete Product Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function upload_products()
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
                    'product_code',
                    'Product Code',
                    'required|is_unique[products.product_code]',
                    [
                        'required'  => 'Product code is required',
                        'is_unique' => 'This Product code already exists'
                    ]
                );
                $this->form_validation->set_rules(
                    'product_name',
                    'Product Name',
                    'required',
                    [
                        'required'  => 'Product name is required',
                    ]
                );

                if ($this->form_validation->run() == FALSE) {
                    $errors[] = "Row {$rowIndex}: " . validation_errors('', '');
                } else {
                    $data[] = [
                        'product_code' => $rowData[0],
                        'product_name' => $rowData[1],
                        'category' => $rowData[2] ?? null,
                        'unit' => $rowData[3] ?? null,
                        'price' => $rowData[4] ?? 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                }
            }
            if (!empty($errors)) {
                $this->_json_response('error', implode('<br>', $errors));
            } else {
                $this->Products_model->insert_batch($data);
                $inserted = count($data);
                $this->_json_response('success', "Successfully inserted {$inserted} product.");
            }
        } catch (Exception $e) {
            log_message('error', 'Upload Product Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_multiple_products()
    {
        $ids = $this->input->post('ids');

        if (empty($ids)) {
            $this->_json_response('error', 'No product selected');
            return;
        }

        try {
            $deleted = $this->Products_model->delete_multiple($ids);

            if ($deleted > 0) {
                $this->_json_response('success', "Deleted {$deleted} product successfully.");
            } else {
                $this->_json_response('error', 'No product were deleted. IDs may not exist.');
            }
        } catch (Exception $e) {
            log_message('error', 'Delete Cheklist Product Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }
}
