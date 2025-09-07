<?php
class Categories_model extends CI_Model
{

    private $table = 'categories';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all()
    {
        $this->db->order_by('id', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }

    public function insert($data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->insert_id();
    }

    public function update($id, $data)
    {
        return $this->db->where('id', $id)->update($this->table, $data);
    }

    public function delete($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }

    public function insert_batch($data)
    {
        return $this->db->insert_batch($this->table, $data);
    }

    public function delete_multiple($ids)
    {
        $this->db->where_in('id', $ids);
        $this->db->delete($this->table);
        return $this->db->affected_rows();
    }
}
