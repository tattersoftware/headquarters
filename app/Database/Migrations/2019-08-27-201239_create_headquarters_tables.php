<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHeadquartersTables extends Migration
{
	public function up()
	{
		// Servers
		$fields = [
			'name'           => ['type' => 'varchar', 'constraint' => 31],
			'url'            => ['type' => 'varchar', 'constraint' => 31],
			'level'          => ['type' => 'int', 'null' => true],
			'synched_at'     => ['type' => 'datetime', 'null' => true],
			'created_at'     => ['type' => 'datetime', 'null' => true],
			'updated_at'     => ['type' => 'datetime', 'null' => true],
			'deleted_at'     => ['type' => 'datetime', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey('name');
		$this->forge->addKey(['deleted_at', 'id']);
		
		$this->forge->createTable('servers');
		
		$db = db_connect();
		$row = [
			'name'   => 'Headquarters',
			'url'    => site_url('agents'),
		];
		$db->table('servers')->insert($row);
		
		// Agents-Servers
		$fields = [
			'agent_id'   => ['type' => 'int', 'unsigned' => true],
			'server_id'  => ['type' => 'int', 'unsigned' => true],
			'created_at'     => ['type' => 'datetime', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey(['agent_id', 'server_id']);
		$this->forge->addKey(['server_id', 'agent_id']);
		
		$this->forge->createTable('agents_servers');
	}

	public function down()
	{
		$this->forge->dropTable('servers');
		$this->forge->dropTable('agents_servers');
	}
}
