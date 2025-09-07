<?php
defined('BASEPATH') or exit('No direct script access allowed');

require FCPATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/**
 * @property CI_Form_validation $form_validation
 * @property CI_Input $input
 * @property CI_DB_query_builder $db
 * @property Users_model $Users_model
 * @property CI_Security $security
 */

class Users extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Users_model');
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

    public function get_users()
    {
        try {
            $table = $this->Users_model->get_all();
            $data = [];
            $row = 1;

            foreach ($table as $td) {
                $row = [];
                $row['checklist'] = '<input type="checkbox" class="delete-checkbox" value="' . $td->id . '">';
                $row['username'] = $td->username;
                $row['role'] = $td->role;
                $row['email'] = $td->email;
                $row['status'] = $td->status;
                $row['action'] = '
                <button class="btn btn-sm btn-warning edit-btn" data-id="' . $td->id . '" data-username="' . $td->username . '"
                data-role="' . $td->role . '" data-email="' . $td->email . '" data-status="' . $td->status . '">
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

    public function _unique_username($name, $id)
    {
        $exists = $this->db->where('username', $name)
            ->where('id !=', $id)
            ->get('users')
            ->row();

        return $exists ? FALSE : TRUE;
    }

    public function update_users()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'User ID',
            'required',
            [
                'required'  => 'Id is required'
            ]
        );
        $this->form_validation->set_rules(
            'username',
            'Username',
            'required[callback__unique_username[' . $id . ']',
            [
                'required' => 'Username is required',
                '_unique_username' => 'This Username already exist'
            ]
        );

        if ($this->form_validation->run() == FALSE) {
            $this->_json_response('error', '<ul>' . validation_errors('<li>', '</li>') . '</ul>');
            return;
        }

        $data = [
            'username' => $this->input->post('username', TRUE),
            'role' => $this->input->post('role', TRUE),
            'email' => $this->input->post('email', TRUE),
            'status' => $this->input->post('status', TRUE),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        try {
            $operation = $this->Users_model->update($id, $data);
            $msg = $operation ? 'User updated successfully!' : 'Failed to update User.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Update User Error' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_users()
    {
        $id = $this->input->post('id', TRUE);

        $this->form_validation->set_rules(
            'id',
            'User ID',
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
            $operation = $this->Users_model->delete($id);
            $msg = $operation ? 'User deleted successfully!' : 'Failed to delete User.';
            $this->_json_response($operation ? 'success' : 'error', $msg);
        } catch (Exception $e) {
            log_message('error', 'Delete User Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }

    public function delete_multiple_users()
    {
        $ids = $this->input->post('ids');

        if (empty($ids)) {
            $this->_json_response('error', 'No User selected');
            return;
        }

        try {
            $deleted = $this->Users_model->delete_multiple($ids);

            if ($deleted > 0) {
                $this->_json_response('success', "Deleted {$deleted} User successfully.");
            } else {
                $this->_json_response('error', 'No User were deleted. IDs may not exist.');
            }
        } catch (Exception $e) {
            log_message('error', 'Delete Cheklist User Error: ' . $e->getMessage());
            $this->_json_response('error', 'Unexpected error: ' . $e->getMessage());
        }
    }
}
