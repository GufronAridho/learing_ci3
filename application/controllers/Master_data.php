<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_DB_query_builder $db
 */

class Master_data extends CI_Controller
{


    public function __construct()
    {
        parent::__construct();
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

    public function categories()
    {
        $data['title'] = 'Categories';
        // $data['results']  = $this->Categories_model->get_all();
        $data['contents'] = $this->load->view('master_data/categories', [], TRUE);

        $this->load->view('layouts/main', $data);
    }
}
