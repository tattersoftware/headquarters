<?php namespace App\Entities;

use App\Models\ServerModel;
use CodeIgniter\Entity;

class Server extends Entity
{
	protected $dates = ['synched_at', 'created_at', 'updated_at', 'deleted_at'];
	
	public function getAgents()
	{
		$servers = new ServerModel();
		return $servers->fetchAgents($this->attributes['id']);
	}
	
	// Ensures a valid URL with trailing slash
	public function getUrl()
	{
		return filter_var($this->attributes['url'], FILTER_VALIDATE_URL) ?
			rtrim($this->attributes['url'], '/') . '/' : null;
	}
	
	// Add an agent-server relation
	public function addAgent($agent)
	{
		return (new ServerModel())->addAgent($this->attributes['id'], $agent);
	}
}
