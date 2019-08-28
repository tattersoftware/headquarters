<?php namespace App\Models;

use Tatter\Agents\Models\AgentModel;

class ServerModel extends BaseModel
{
	protected $table      = 'servers';
	protected $primaryKey = 'id';

	protected $returnType = 'App\Entities\Server';
	protected $useSoftDeletes = true;

	protected $allowedFields = ['name', 'url', 'level', 'synched_at'];

	protected $useTimestamps = true;

	protected $validationRules    = [];
	protected $validationMessages = [];
	protected $skipValidation     = true;
	
	// Store of pre-fetched relatives
	protected static $agents = [];

	// Pre-loads all agents into the model's static property
	// If a server ID was supplied then return its agents
	public function fetchAgents($serverId = null, $refresh = false)
	{
		// Check if already loaded
		if ($refresh || empty(self::$agents))
		{
			// Get all the agents indexed by their ID
			$agents = [];
			foreach ((new AgentModel())->withDeleted()->findAll() as $agent)
			{
				$agents[$agent->id] = $agent;
			}
			
			// Get all agent relations
			foreach ($this->db->table('agents_servers')->get()->getResult() as $row)
			{
				// Index by server ID & agent ID
				self::$agents[$row->server_id][$row->agent_id] =& $agents[$row->agent_id];
			}
		}
		
		if ($serverId)
			return self::$agents[$serverId] ?? [];
		return self::$agents;
	}
	
	// Add an agent-server relation
	public function addAgent($server, $agent)
	{
		$row = [
			'agent_id'   => $agent->id ?? $agent,
			'server_id'  => $server->id ?? $server,
			'created_at' => date('Y-m-d H:i:s'),
		];
		
		// Check if it already exists
		$agents = $this->fetchAgents($row['server_id']);
		if (isset($agents[$row['agent_id']]))
			return true;
		
		return $this->db->table('agents_servers')->insert($row);
	}
}
