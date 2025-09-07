<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_product_colomn extends CI_Migration
{
    public function up()
    {
        $fields = [
            'img_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => TRUE,
                'after'      => 'price'
            ],
        ];
        $this->dbforge->add_column('products', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('products', 'img_file');
    }
}
