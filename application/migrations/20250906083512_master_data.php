<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Master_data extends CI_Migration
{

    public function up()
    {
        // ========================
        // Users Table
        // ========================
        $this->dbforge->add_field(array(
            'id' => array('type' => 'SERIAL', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'username' => array('type' => 'VARCHAR', 'constraint' => '255', 'unique' => TRUE),
            'password' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'email' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'role' => array('type' => "VARCHAR", 'constraint' => '20', 'default' => 'staff'),
            'status' => array('type' => 'SMALLINT', 'default' => 1),
            'created_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'updated_at' => array('type' => 'TIMESTAMP', 'null' => TRUE)
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('users', TRUE);

        // ========================
        // Categories
        // ========================
        $this->dbforge->add_field(array(
            'id' => array('type' => 'SERIAL', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'category_name' => array('type' => 'VARCHAR', 'constraint' => '100', 'unique' => TRUE),
            'description' => array('type' => 'TEXT', 'null' => TRUE),
            'created_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'updated_at' => array('type' => 'TIMESTAMP', 'null' => TRUE)
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('categories', TRUE);

        // ========================
        // Units
        // ========================
        $this->dbforge->add_field(array(
            'id' => array('type' => 'SERIAL', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'unit_name' => array('type' => 'VARCHAR', 'constraint' => '50', 'unique' => TRUE),
            'description' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE),
            'created_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'updated_at' => array('type' => 'TIMESTAMP', 'null' => TRUE)
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('units', TRUE);

        // ========================
        // Products
        // ========================
        $this->dbforge->add_field(array(
            'id' => array('type' => 'SERIAL', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'product_code' => array('type' => 'VARCHAR', 'constraint' => '50', 'unique' => TRUE),
            'product_name' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'category' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE),
            'unit' => array('type' => 'VARCHAR', 'constraint' => '50', 'null' => TRUE),
            'price' => array('type' => 'NUMERIC', 'constraint' => '10,2', 'default' => 0),
            'created_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'updated_at' => array('type' => 'TIMESTAMP', 'null' => TRUE)
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('products', TRUE);

        // ========================
        // Suppliers
        // ========================
        $this->dbforge->add_field(array(
            'id' => array('type' => 'SERIAL', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'supplier_code' => array('type' => 'VARCHAR', 'constraint' => '50', 'unique' => TRUE),
            'supplier_name' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'contact_name' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE),
            'phone' => array('type' => 'VARCHAR', 'constraint' => '50', 'null' => TRUE),
            'email' => array('type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE),
            'address' => array('type' => 'TEXT', 'null' => TRUE),
            'created_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'updated_at' => array('type' => 'TIMESTAMP', 'null' => TRUE)
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('suppliers', TRUE);

        // ========================
        // Customers
        // ========================
        $this->dbforge->add_field(array(
            'id' => array('type' => 'SERIAL', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'customer_code' => array('type' => 'VARCHAR', 'constraint' => '50', 'unique' => TRUE),
            'customer_name' => array('type' => 'VARCHAR', 'constraint' => '255'),
            'contact_name' => array('type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE),
            'phone' => array('type' => 'VARCHAR', 'constraint' => '50', 'null' => TRUE),
            'email' => array('type' => 'VARCHAR', 'constraint' => '255', 'null' => TRUE),
            'address' => array('type' => 'TEXT', 'null' => TRUE),
            'created_at' => array('type' => 'TIMESTAMP', 'null' => TRUE),
            'updated_at' => array('type' => 'TIMESTAMP', 'null' => TRUE)
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('customers', TRUE);
    }

    public function down()
    {
        $this->dbforge->drop_table('customers');
        $this->dbforge->drop_table('suppliers');
        $this->dbforge->drop_table('products');
        $this->dbforge->drop_table('units');
        $this->dbforge->drop_table('categories');
        $this->dbforge->drop_table('users');
    }
}
