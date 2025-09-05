<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_data extends CI_Controller
{

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

    public function categories()
    {
        $data['title'] = 'Categories';
        $data['contents'] = $this->load->view('master_data/categories', [], TRUE);

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
}
